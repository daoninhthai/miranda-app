<?php
/**
 * Copyright © 2013-2020 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magetop\SellerUspsShipping\Model\Carrier;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Module\Dir;
use Magento\Sales\Model\Order\Shipment;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Rate\Result;
use Magento\Framework\Xml\Security;
use Magento\Framework\Measure\Weight;
use Magento\Framework\Measure\Length;

/**
 * DHL International (API v1.4)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SellerUspsShipping extends \Magento\Usps\Model\Carrier
{
    /**
     * Weight precision
     *
     * @var int
     */
    private static $weightPrecision = 10;
    /**
     * Retrieve information from carrier configuration
     *
     * @param   string $field
     * @param   string $vendorId
     * @return  mixed
     */
    public function getVendorConfigData($field, $vendorId)
    {
        $om  = \Magento\Framework\App\ObjectManager::getInstance();
        $config = $om->create('Magetop\SellerUspsShipping\Model\SellerUspsShipping')->getCollection()->addFieldToFilter('seller_id',$vendorId)->getFirstItem();
        return $config[$field];
    }
    
    /**
     * Get config data from admin
     *
     * @param   string $patch
     * @return  config
     */
    public function getAdminConfigData($patch)
    {
        $om  = \Magento\Framework\App\ObjectManager::getInstance();
        $config = $om->create('\Magento\Framework\App\Config\ScopeConfigInterface')->getValue($patch, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        return $config;
    }
    
    /**
     * Group Items by vendor
     * @return Ambigous <multitype:multitype: , unknown>
     */
    public function groupItemsByVendor(){
        $quotes = array();
        foreach($this->_request->getAllItems() as $item) {
            $product = $item->getProduct()->load($item->getProductId());
            if($item->getParentItem() || $product->isVirtual()) continue;
            $mkProductCollection = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magetop\Marketplace\Model\Products')->getCollection()
                                                                                      ->addFieldToFilter('product_id',$item->getProductId())
                                                                                      ->addFieldToFilter('status',1);
            //Kien 19/5/2020 - update filter seller approve        
            $tableMKuser = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Framework\App\ResourceConnection')->getTableName('multivendor_user');
            $mkProductCollection->getSelect()->joinLeft(array('mk_user'=>$tableMKuser),'main_table.user_id = mk_user.user_id',array())->where('mk_user.userstatus = 1'); 
            $sellerId = 0;
            $productOption = $item->getProductOptions();
            $infoBuyRequest = $productOption['info_buyRequest'];
            if(@$infoBuyRequest['assignproduct_id']){
                $SellerAssignProduct = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magetop\SellerAssignProduct\Model\SellerAssignProduct')->load($infoBuyRequest['assignproduct_id']);
                $sellerId = $SellerAssignProduct->getSellerId();
                $multivendor_assign_product_id = $infoBuyRequest['assignproduct_id'];                    
            }else{
				if(count($mkProductCollection))
				{
					foreach($mkProductCollection as $mkProductCollect)
					{
						$sellerId = $mkProductCollect->getUserId();
                        $multivendor_assign_product_id = 0;                            
						break;
					}
				}
            }
            if($sellerId) {
                $vendorId = $sellerId;
                /*Get item by vendor id*/
                if(!isset($quotes[$vendorId])) $quotes[$vendorId] = [];
                $quotes[$vendorId][] = $item;
            } else {
                $quotes['no_vendor'][] = $item;
            }
        }
        return $quotes;
    }
    
    /**
     * Build RateV3 request, send it to USPS gateway and retrieve quotes in XML format
     *
     * @link https://www.usps.com/webtools/htm/Rate-Calculators-v2-3.htm
     * @return Result
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _getXmlQuotes()
    {
        $rowRequest = $this->_rawRequest;
        $this->_result = $this->_rateFactory->create();
        // make separate request for Smart Post method
        $quotes = $this->groupItemsByVendor();

        foreach($quotes as $vendorId=>$items) {
            if (!$this->getVendorConfigData("enable", $vendorId)) continue;
            $checkCountryAllow = $this->checkAvailableShipCountriesForVendor($rowRequest, $vendorId);
            if (false == $checkCountryAllow || $checkCountryAllow instanceof \Magento\Quote\Model\Quote\Address\RateResult\Error) {
                continue;
            }
            
            $seller = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magetop\Marketplace\Model\Sellers')->getCollection()->addFieldToFilter('user_id',$vendorId)->getFirstItem();
            $vendor = $seller->getData();
            
            // The origin address(shipper) must be only in USA
            if (!$this->_isUSCountry($vendor['country'])) {
                continue;
            }

            $weight = 0;
            $amount = 0;
            foreach ($items as $item) {
                $weight += $item->getWeight() * $item->getQty();
                $amount += $item->getBaseRowTotal();
            }
            
            $weight = $this->getTotalNumOfBoxes($weight);
            $weightOunces =    round(($weight - floor($weight)) * self::OUNCES_POUND, self::$weightPrecision);

            $vendorRequest = new \Magento\Framework\DataObject();
            $vendorRequest->setData([
                'country'   => $vendor['country'],
                'postcode'  => $vendor['zipcode'],
                'city'      => $vendor['city'],
                'weight'    => $weight,
                'amount'    => $amount,
                'vendor_id' => $vendor['user_id'],
                'name'      => $vendor['storetitle']
            ]);

            $userId = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ?
                $this->getVendorConfigData('userid',$vendorId)
                : $this->getConfigData('userid');

            $password = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ?
                $this->getVendorConfigData('password',$vendorId)
                : $this->getConfigData('password');
            
            if ($this->_isUSCountry($rowRequest->getDestCountryId())) {
                $xml = $this->_xmlElFactory->create(
                    ['data' => '<?xml version="1.0" encoding="UTF-8"?><RateV4Request/>']
                );
                $xml->addAttribute('USERID', $userId);
                // according to usps v4 documentation
                $xml->addChild('Revision', '2');

                $package = $xml->addChild('Package');
                $package->addAttribute('ID', 0);
                $service = $this->getCode('service_to_code', $rowRequest->getService());
                if (!$service) {
                    $service = $rowRequest->getService();
                }
                if ($rowRequest->getContainer() == 'FLAT RATE BOX' || $rowRequest->getContainer() == 'FLAT RATE ENVELOPE') {
                    $service = 'Priority';
                }
                $package->addChild('Service', $service);

                // no matter Letter, Flat or Parcel, use Parcel
                if ($rowRequest->getService() == 'FIRST CLASS' || $rowRequest->getService() == 'FIRST CLASS HFP COMMERCIAL') {
                    $package->addChild('FirstClassMailType', 'PARCEL');
                }
                $package->addChild('ZipOrigination', $vendor['zipcode']);
                //only 5 chars avaialble
                $package->addChild('ZipDestination', substr($rowRequest->getDestPostal(), 0, 5));
                $package->addChild('Pounds', floor($weight));
                $package->addChild('Ounces', $weightOunces);
                // Because some methods don't accept VARIABLE and (NON)RECTANGULAR containers
                $package->addChild('Container', $rowRequest->getContainer());
                $package->addChild('Size', $rowRequest->getSize());
                if ($rowRequest->getSize() == 'LARGE') {
                    $package->addChild('Width', $rowRequest->getWidth());
                    $package->addChild('Length', $rowRequest->getLength());
                    $package->addChild('Height', $rowRequest->getHeight());
                    if ($rowRequest->getContainer() == 'NONRECTANGULAR' || $rowRequest->getContainer() == 'VARIABLE') {
                        $package->addChild('Girth', $rowRequest->getGirth());
                    }
                }
                $package->addChild('Machinable', $rowRequest->getMachinable());

                $api = 'RateV4';
            } else {
                $xml = $this->_xmlElFactory->create(
                    ['data' => '<?xml version = "1.0" encoding = "UTF-8"?><IntlRateV2Request/>']
                );
                $xml->addAttribute('USERID', $userId);
                // according to usps v4 documentation
                $xml->addChild('Revision', '2');

                $package = $xml->addChild('Package');
                $package->addAttribute('ID', 0);
                $package->addChild('Pounds', floor($weight));
                $package->addChild('Ounces', $weightOunces);
                $package->addChild('MailType', 'All');
                $package->addChild('ValueOfContents', $amount);
                $package->addChild('Country', $rowRequest->getDestCountryName());
                $package->addChild('Container', $rowRequest->getContainer());
                $package->addChild('Size', $rowRequest->getSize());
                $width = $length = $height = $girth = '';
                if ($rowRequest->getSize() == 'LARGE') {
                    $width = $rowRequest->getWidth();
                    $length = $rowRequest->getLength();
                    $height = $rowRequest->getHeight();
                    if ($rowRequest->getContainer() == 'NONRECTANGULAR') {
                        $girth = $rowRequest->getGirth();
                    }
                }
                $package->addChild('Width', $width);
                $package->addChild('Length', $length);
                $package->addChild('Height', $height);
                $package->addChild('Girth', $girth);

                $api = 'IntlRateV2';
            }
            $request = $xml->asXML();

            $responseBody = $this->_getCachedQuotes($request);
            if ($responseBody === null) {
                $debugData = ['request' => $this->filterDebugData($request)];
                try {
                    $url = $this->getConfigData('gateway_url');
                    if (!$url) {
                        $url = $this->_defaultGatewayUrl;
                    }
                    $client = $this->_httpClientFactory->create();
                    $client->setUri($url);
                    $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
                    $client->setParameterGet('API', $api);
                    $client->setParameterGet('XML', $request);
                    $response = $client->request();
                    $responseBody = $response->getBody();

                    $debugData['result'] = $responseBody;
                    $this->_setCachedQuotes($request, $responseBody);
                } catch (\Exception $e) {
                    $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
                    $responseBody = '';
                }
                $this->_debug($debugData);
            }

            $preparedGeneral = $this->_parseXmlResponseVendor($responseBody,$vendorRequest);
            if (!$preparedGeneral->getError()) {
                $this->_result->append($preparedGeneral);
            }
        }

        return $this->_result ;
    }
    
    /**
     * Parse calculated rates
     *
     * @param string $response
     * @return Result
     * @link https://www.usps.com/webtools/htm/Rate-Calculators-v2-3.htm
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _parseXmlResponseVendor($response,$vendorRequest)
    {
        $methods = $this->getAdminConfigData('carriers/usps/api_type')=='seller'?
            $this->getVendorConfigData('allowed_methods',$vendorRequest->getData("vendor_id")):
            $this->getConfigData('allowed_methods');
        $allowedMethods = explode(',', $methods??'');
        
        $r = $this->_rawRequest;
        $costArr = [];
        $priceArr = [];
        if (strlen(trim($response)) > 0) {
            if (@strpos(trim($response), '<?xml') === 0) {
                if (@strpos($response, '<?xml version="1.0"?>') !== false) {
                    $response = str_replace(
                        '<?xml version="1.0"?>',
                        '<?xml version="1.0" encoding="ISO-8859-1"?>',
                        $response
                    );
                }
                $xml = $this->parseXml($response);

                if (is_object($xml)) {
                    $serviceCodeToActualNameMap = [];
                    /**
                     * US Rates
                     */
                    if ($this->_isUSCountry($r->getDestCountryId())) {
                        if (is_object($xml->Package) && is_object($xml->Package->Postage)) {
                            foreach ($xml->Package->Postage as $postage) {
                                $serviceName = $this->_filterServiceName((string)$postage->MailService);
                                $_serviceCode = $this->getCode('method_to_code', $serviceName);
                                $serviceCode = $_serviceCode ? $_serviceCode : (string)$postage->attributes()->CLASSID;
                                $serviceCodeToActualNameMap[$serviceCode] = $serviceName;
                                if (in_array($serviceCode, $allowedMethods)) {
                                    $costArr[$serviceCode] = (string)$postage->Rate;
                                    $priceArr[$serviceCode] = $this->getMethodPriceVendor($vendorRequest,
                                        (string)$postage->Rate,
                                        $serviceCode
                                    );
                                }
                            }
                            asort($priceArr);
                        }
                    } else {
                        /*
                         * International Rates
                         */
                        if (is_object($xml->Package) && is_object($xml->Package->Service)) {
                            foreach ($xml->Package->Service as $service) {
                                $serviceName = $this->_filterServiceName((string)$service->SvcDescription);
                                $serviceCode = 'INT_' . (string)$service->attributes()->ID;
                                $serviceCodeToActualNameMap[$serviceCode] = $serviceName;
                                if (!$this->isServiceAvailable($service)) {
                                    continue;
                                }
                                if (in_array($serviceCode, $allowedMethods)) {
                                    $costArr[$serviceCode] = (string)$service->Postage;
                                    $priceArr[$serviceCode] = $this->getMethodPriceVendor($vendorRequest,
                                        (string)$service->Postage,
                                        $serviceCode
                                    );
                                }
                            }
                            asort($priceArr);
                        }
                    }
                }
            }
        }

        $result = $this->_rateFactory->create();
        if (empty($priceArr)) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier('usps');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            foreach ($priceArr as $method => $price) {
                $rate = $this->_rateMethodFactory->create();
                $rate->setCarrier('usps');
                $rate->setCarrierTitle($this->getConfigData('title').' by seller : '.$vendorRequest->getData("name"));
                $rate->setMethod($method.'||'.$vendorRequest->getData("vendor_id"));
                $rate->setMethodTitle(
                    isset(
                        $serviceCodeToActualNameMap[$method]
                    ) ? $serviceCodeToActualNameMap[$method] : $this->getCode(
                        'method',
                        $method
                    )
                );
                $rate->setVendorId($vendorRequest->getData("vendor_id"));
                $rate->setCost($costArr[$method]);
                $rate->setPrice($price);
                $result->append($rate);
            }
        }

        return $result;
    }
    
    /**
     * Calculate price considering free shipping and handling fee
     * @param object $vendorRequest
     * @param string $cost
     * @param string $method
     * @return float|string
     * @api
     */
    public function getMethodPriceVendor($vendorRequest ,$cost, $method = '')
    {               
        $configFreeMethod = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ?
            $this->getVendorConfigData('free_method',$vendorRequest->getData("vendor_id"))
            : $this->getConfigData('free_method');

        $freeShippingEnable =  $this->getAdminConfigData('carriers/usps/api_type')=='seller' ?
            $this->getVendorConfigData("free_shipping_enable",$vendorRequest->getData("vendor_id"))
            : $this->getConfigData("free_shipping_enable");

        $freeShippingSubtotal=  $this->getAdminConfigData('carriers/usps/api_type')=='seller' ?
            $this->getVendorConfigData("free_shipping_subtotal",$vendorRequest->getData("vendor_id"))
            : $this->getConfigData("free_shipping_subtotal");

        return $method == $configFreeMethod && $freeShippingEnable && $freeShippingSubtotal <= $vendorRequest->getData("amount") ?
            '0.00' : $this->getFinalPriceWithHandlingFee(
                $cost
            );
    }
    
    /**
     * @param \Magento\Framework\DataObject $request
     * @return $this|bool|false|\Magento\Framework\Model\AbstractModel
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function checkAvailableShipCountriesForVendor(\Magento\Framework\DataObject $request,$vendorId)
    {
        $speCountriesAllow = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ?
            $this->getVendorConfigData('sallowspecific',$vendorId)
            : $this->getConfigData('sallowspecific');

        /*
         * for specific countries, the flag will be 1
         */
        if ($speCountriesAllow && $speCountriesAllow == 1) {

            $showMethod = $this->getConfigData('showmethod');
            $availableCountries = [];

            $specificcountry = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ?
                $this->getVendorConfigData('specificcountry',$vendorId)
                : $this->getConfigData('specificcountry');

            if ($specificcountry) {
                $availableCountries = explode(',', $specificcountry??'');
            }
            if ($availableCountries && in_array($request->getDestCountryId(), $availableCountries)) {
                return $this;
            } elseif ($showMethod && (!$availableCountries || $availableCountries && !in_array(
                        $request->getDestCountryId(),
                        $availableCountries
                    ))
            ) {
                /** @var Error $error */
                $error = $this->_rateErrorFactory->create();
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $errorMsg = $this->getConfigData('specificerrmsg');
                $error->setErrorMessage(
                    $errorMsg ? $errorMsg : __(
                        'Sorry, but we can\'t deliver to the destination country with this shipping module.'
                    )
                );

                return $error;
            } else {
                /*
                 * The admin set not to show the shipping module if the delivery country is not within specific countries
                 */
                return false;
            }
        }

        return true;
    }
    
    /**
     * Form XML for US shipment request
     * As integration guide it is important to follow appropriate sequence for tags e.g.: <FromLastName /> must be
     * after <FromFirstName />
     *
     * @param \Magento\Framework\DataObject $request
     * @return string
     */
    protected function _formUsExpressShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $packageParams = $request->getPackageParams();

        $packageWeight = $request->getPackageWeight();
        if ($packageParams->getWeightUnits() != Weight::OUNCE) {
            $packageWeight = round(
                (float) $this->_carrierHelper->convertMeasureWeight(
                    (float) $request->getPackageWeight(),
                    $packageParams->getWeightUnits(),
                    Weight::OUNCE
                )
            );
        }

        list($fromZip5, $fromZip4) = $this->_parseZip($request->getShipperAddressPostalCode());
        list($toZip5, $toZip4) = $this->_parseZip($request->getRecipientAddressPostalCode(), true);

        $userId = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData('userid',$request->getVendorId())
            : $this->getConfigData('userid');

        $password = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData('password',$request->getVendorId())
            : $this->getConfigData('password');


        $rootNode = 'ExpressMailLabelRequest';
        // the wrap node needs for remove xml declaration above
        $xmlWrap = $this->_xmlElFactory->create(['data' => '<?xml version = "1.0" encoding = "UTF-8"?><wrap/>']);
        $xml = $xmlWrap->addChild($rootNode);
        $xml->addAttribute('USERID', $userId);
        $xml->addAttribute('PASSWORD', $password);
        $xml->addChild('Option');
        $xml->addChild('Revision');
        $xml->addChild('EMCAAccount');
        $xml->addChild('EMCAPassword');
        $xml->addChild('ImageParameters');
        $xml->addChild('FromFirstName', $request->getShipperContactPersonFirstName());
        $xml->addChild('FromLastName', $request->getShipperContactPersonLastName());
        $xml->addChild('FromFirm', $request->getShipperContactCompanyName());
        $xml->addChild('FromAddress1', $request->getShipperAddressStreet2());
        $xml->addChild('FromAddress2', $request->getShipperAddressStreet1());
        $xml->addChild('FromCity', $request->getShipperAddressCity());
        $xml->addChild('FromState', $request->getShipperAddressStateOrProvinceCode());
        $xml->addChild('FromZip5', $fromZip5);
        $xml->addChild('FromZip4', $fromZip4);
        $xml->addChild('FromPhone', $request->getShipperContactPhoneNumber());
        $xml->addChild('ToFirstName', $request->getRecipientContactPersonFirstName());
        $xml->addChild('ToLastName', $request->getRecipientContactPersonLastName());
        $xml->addChild('ToFirm', $request->getRecipientContactCompanyName());
        $xml->addChild('ToAddress1', $request->getRecipientAddressStreet2());
        $xml->addChild('ToAddress2', $request->getRecipientAddressStreet1());
        $xml->addChild('ToCity', $request->getRecipientAddressCity());
        $xml->addChild('ToState', $request->getRecipientAddressStateOrProvinceCode());
        $xml->addChild('ToZip5', $toZip5);
        $xml->addChild('ToZip4', $toZip4);
        $xml->addChild('ToPhone', $request->getRecipientContactPhoneNumber());
        $xml->addChild('WeightInOunces', $packageWeight);
        $xml->addChild('WaiverOfSignature', $packageParams->getDeliveryConfirmation());
        $xml->addChild('POZipCode');
        $xml->addChild('ImageType', 'PDF');

        $xml = $xmlWrap->{$rootNode}->asXML();

        return $xml;
    }
    
    /**
     * Form XML for US Signature Confirmation request
     * As integration guide it is important to follow appropriate sequence for tags e.g.: <FromLastName /> must be
     * after <FromFirstName />
     *
     * @param \Magento\Framework\DataObject $request
     * @param string $serviceType
     * @return string
     * @throws \Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _formUsSignatureConfirmationShipmentRequest(\Magento\Framework\DataObject $request, $serviceType)
    {
        switch ($serviceType) {
            case 'PRIORITY':
            case 'Priority':
                $serviceType = 'Priority';
                break;
            case 'FIRST CLASS':
            case 'First Class':
                $serviceType = 'First Class';
                break;
            case 'STANDARD':
            case 'Standard Post':
            case 'Retail Ground':
                $serviceType = 'Retail Ground';
                break;
            case 'MEDIA':
            case 'Media':
                $serviceType = 'Media Mail';
                break;
            case 'LIBRARY':
            case 'Library':
                $serviceType = 'Library Mail';
                break;
            default:
                throw new \Exception(__('Service type does not match'));
        }


        $packageParams = $request->getPackageParams();
        $packageWeight = $request->getPackageWeight();
        if ($packageParams->getWeightUnits() != Weight::OUNCE) {
            $packageWeight = round(
                (float) $this->_carrierHelper->convertMeasureWeight(
                    (float) $request->getPackageWeight(),
                    $packageParams->getWeightUnits(),
                    Weight::OUNCE
                )
            );
        }

        list($fromZip5, $fromZip4) = $this->_parseZip($request->getShipperAddressPostalCode());
        list($toZip5, $toZip4) = $this->_parseZip($request->getRecipientAddressPostalCode(), true);

        if ($this->getConfigData('mode')) {
            $rootNode = 'SignatureConfirmationV3.0Request';
        } else {
            $rootNode = 'SigConfirmCertifyV3.0Request';
        }

        $userId = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData('userid',$request->getVendorId())
            : $this->getConfigData('userid');

        $password = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData('password',$request->getVendorId())
            : $this->getConfigData('password');

        

        // the wrap node needs for remove xml declaration above
        $xmlWrap = $this->_xmlElFactory->create(['data' => '<?xml version = "1.0" encoding = "UTF-8"?><wrap/>']);
        $xml = $xmlWrap->addChild($rootNode);
        $xml->addAttribute('USERID', $userId);
        $xml->addChild('Option', 1);
        $xml->addChild('ImageParameters');
        $xml->addChild('FromName', $request->getShipperContactPersonName());
        $xml->addChild('FromFirm', $request->getShipperContactCompanyName());
        $xml->addChild('FromAddress1', $request->getShipperAddressStreet2());
        $xml->addChild('FromAddress2', $request->getShipperAddressStreet1());
        $xml->addChild('FromCity', $request->getShipperAddressCity());
        $xml->addChild('FromState', $request->getShipperAddressStateOrProvinceCode());
        $xml->addChild('FromZip5', $fromZip5);
        $xml->addChild('FromZip4', $fromZip4);
        $xml->addChild('ToName', $request->getRecipientContactPersonName());
        $xml->addChild('ToFirm', $request->getRecipientContactCompanyName());
        $xml->addChild('ToAddress1', $request->getRecipientAddressStreet2());
        $xml->addChild('ToAddress2', $request->getRecipientAddressStreet1());
        $xml->addChild('ToCity', $request->getRecipientAddressCity());
        $xml->addChild('ToState', $request->getRecipientAddressStateOrProvinceCode());
        $xml->addChild('ToZip5', $toZip5);
        $xml->addChild('ToZip4', $toZip4);
        $xml->addChild('WeightInOunces', $packageWeight);
        $xml->addChild('ServiceType', $serviceType);
        $xml->addChild('WaiverOfSignature', $packageParams->getDeliveryConfirmation());
        $xml->addChild('ImageType', 'PDF');

        $xml = $xmlWrap->{$rootNode}->asXML();

        return $xml;
    }
    
    /**
     * Form XML for international shipment request
     * As integration guide it is important to follow appropriate sequence for tags e.g.: <FromLastName /> must be
     * after <FromFirstName />
     *
     * @param \Magento\Framework\DataObject $request
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _formIntlShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $packageParams = $request->getPackageParams();
        $height = $packageParams->getHeight();
        $width = $packageParams->getWidth();
        $length = $packageParams->getLength();
        $girth = $packageParams->getGirth();
        $packageWeight = $request->getPackageWeight();
        if ($packageParams->getWeightUnits() != Weight::POUND) {
            $packageWeight = $this->_carrierHelper->convertMeasureWeight(
                (float) $request->getPackageWeight(),
                $packageParams->getWeightUnits(),
                Weight::POUND
            );
        }
        if ($packageParams->getDimensionUnits() != Length::INCH) {
            $length = round(
                (float) $this->_carrierHelper->convertMeasureDimension(
                    (float) $packageParams->getLength(),
                    $packageParams->getDimensionUnits(),
                    Length::INCH
                )
            );
            $width = round(
                (float) $this->_carrierHelper->convertMeasureDimension(
                    (float) $packageParams->getWidth(),
                    $packageParams->getDimensionUnits(),
                    Length::INCH
                )
            );
            $height = round(
                (float) $this->_carrierHelper->convertMeasureDimension(
                    (float) $packageParams->getHeight(),
                    $packageParams->getDimensionUnits(),
                    Length::INCH
                )
            );
        }
        if ($packageParams->getGirthDimensionUnits() != Length::INCH) {
            $girth = round(
                (float) $this->_carrierHelper->convertMeasureDimension(
                    (float) $packageParams->getGirth(),
                    $packageParams->getGirthDimensionUnits(),
                    Length::INCH
                )
            );
        }

        $container = $request->getPackagingType();
        switch ($container) {
            case 'VARIABLE':
                $container = 'VARIABLE';
                break;
            case 'FLAT RATE ENVELOPE':
                $container = 'FLATRATEENV';
                break;
            case 'FLAT RATE BOX':
                $container = 'FLATRATEBOX';
                break;
            case 'RECTANGULAR':
                $container = 'RECTANGULAR';
                break;
            case 'NONRECTANGULAR':
                $container = 'NONRECTANGULAR';
                break;
            default:
                $container = 'VARIABLE';
        }
        $shippingMethod = $request->getShippingMethod();
        list($fromZip5, $fromZip4) = $this->_parseZip($request->getShipperAddressPostalCode());

        // the wrap node needs for remove xml declaration above
        $xmlWrap = $this->_xmlElFactory->create(['data' => '<?xml version = "1.0" encoding = "UTF-8"?><wrap/>']);
        $method = '';
        $service = $this->getCode('service_to_code', $shippingMethod);
        if ($service == 'Priority') {
            $method = 'Priority';
            $rootNode = 'PriorityMailIntlRequest';
            $xml = $xmlWrap->addChild($rootNode);
        } else {
            if ($service == 'First Class') {
                $method = 'FirstClass';
                $rootNode = 'FirstClassMailIntlRequest';
                $xml = $xmlWrap->addChild($rootNode);
            } else {
                $method = 'Express';
                $rootNode = 'ExpressMailIntlRequest';
                $xml = $xmlWrap->addChild($rootNode);
            }
        }

        $userId = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData('userid',$request->getVendorId())
            : $this->getConfigData('userid');

        $password = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData('password',$request->getVendorId())
            : $this->getConfigData('password');




        $xml->addAttribute('USERID', $userId);
        $xml->addAttribute('PASSWORD', $password);
        $xml->addChild('Option');
        $xml->addChild('Revision', self::DEFAULT_REVISION);
        $xml->addChild('ImageParameters');
        $xml->addChild('FromFirstName', $request->getShipperContactPersonFirstName());
        $xml->addChild('FromLastName', $request->getShipperContactPersonLastName());
        $xml->addChild('FromFirm', $request->getShipperContactCompanyName());
        $xml->addChild('FromAddress1', $request->getShipperAddressStreet2());
        $xml->addChild('FromAddress2', $request->getShipperAddressStreet1());
        $xml->addChild('FromCity', $request->getShipperAddressCity());
        $xml->addChild('FromState', $request->getShipperAddressStateOrProvinceCode());
        $xml->addChild('FromZip5', $fromZip5);
        $xml->addChild('FromZip4', $fromZip4);
        $xml->addChild('FromPhone', $request->getShipperContactPhoneNumber());
        if ($method != 'FirstClass') {
            if ($request->getReferenceData()) {
                $referenceData = $request->getReferenceData() . ' P' . $request->getPackageId();
            } else {
                $referenceData = $request->getOrderShipment()->getOrder()->getIncrementId() .
                    ' P' .
                    $request->getPackageId();
            }
            $xml->addChild('FromCustomsReference', 'Order #' . $referenceData);
        }
        $xml->addChild('ToFirstName', $request->getRecipientContactPersonFirstName());
        $xml->addChild('ToLastName', $request->getRecipientContactPersonLastName());
        $xml->addChild('ToFirm', $request->getRecipientContactCompanyName());
        $xml->addChild('ToAddress1', $request->getRecipientAddressStreet1());
        $xml->addChild('ToAddress2', $request->getRecipientAddressStreet2());
        $xml->addChild('ToCity', $request->getRecipientAddressCity());
        $xml->addChild('ToProvince', $request->getRecipientAddressStateOrProvinceCode());
        $xml->addChild('ToCountry', $this->_getCountryName($request->getRecipientAddressCountryCode()));
        $xml->addChild('ToPostalCode', $request->getRecipientAddressPostalCode());
        $xml->addChild('ToPOBoxFlag', 'N');
        $xml->addChild('ToPhone', $request->getRecipientContactPhoneNumber());
        $xml->addChild('ToFax');
        $xml->addChild('ToEmail');
        if ($method != 'FirstClass') {
            $xml->addChild('NonDeliveryOption', 'Return');
        }
        if ($method == 'FirstClass') {
            if (stripos($shippingMethod, 'Letter') !== false) {
                $xml->addChild('FirstClassMailType', 'LETTER');
            } else {
                if (stripos($shippingMethod, 'Flat') !== false) {
                    $xml->addChild('FirstClassMailType', 'FLAT');
                } else {
                    $xml->addChild('FirstClassMailType', 'PARCEL');
                }
            }
        }
        if ($method != 'FirstClass') {
            $xml->addChild('Container', $container);
        }
        $shippingContents = $xml->addChild('ShippingContents');
        $packageItems = $request->getPackageItems();
        // get countries of manufacture
        $countriesOfManufacture = [];
        $productIds = [];
        foreach ($packageItems as $itemShipment) {
            $item = new \Magento\Framework\DataObject();
            $item->setData($itemShipment);

            $productIds[] = $item->getProductId();
        }
        $productCollection = $this->_productCollectionFactory->create()->addStoreFilter(
            $request->getStoreId()
        )->addFieldToFilter(
            'entity_id',
            ['in' => $productIds]
        )->addAttributeToSelect(
            'country_of_manufacture'
        );
        foreach ($productCollection as $product) {
            $countriesOfManufacture[$product->getId()] = $product->getCountryOfManufacture();
        }

        $packagePoundsWeight = $packageOuncesWeight = 0;
        // for ItemDetail
        foreach ($packageItems as $itemShipment) {
            $item = new \Magento\Framework\DataObject();
            $item->setData($itemShipment);

            $itemWeight = $item->getWeight() * $item->getQty();
            if ($packageParams->getWeightUnits() != Weight::POUND) {
                $itemWeight = $this->_carrierHelper->convertMeasureWeight(
                    $itemWeight,
                    $packageParams->getWeightUnits(),
                    Weight::POUND
                );
            }
            if (!empty($countriesOfManufacture[$item->getProductId()])) {
                $countryOfManufacture = $this->_getCountryName($countriesOfManufacture[$item->getProductId()]);
            } else {
                $countryOfManufacture = '';
            }
            $itemDetail = $shippingContents->addChild('ItemDetail');
            $itemDetail->addChild('Description', $item->getName());
            $ceiledQty = ceil($item->getQty());
            if ($ceiledQty < 1) {
                $ceiledQty = 1;
            }
            $individualItemWeight = $itemWeight / $ceiledQty;
            $itemDetail->addChild('Quantity', $ceiledQty);
            $itemDetail->addChild('Value', $item->getCustomsValue() * $item->getQty());
            list($individualPoundsWeight, $individualOuncesWeight) = $this->_convertPoundOunces($individualItemWeight);
            $itemDetail->addChild('NetPounds', $individualPoundsWeight);
            $itemDetail->addChild('NetOunces', $individualOuncesWeight);
            $itemDetail->addChild('HSTariffNumber', 0);
            $itemDetail->addChild('CountryOfOrigin', $countryOfManufacture);

            list($itemPoundsWeight, $itemOuncesWeight) = $this->_convertPoundOunces($itemWeight);
            $packagePoundsWeight += $itemPoundsWeight;
            $packageOuncesWeight += $itemOuncesWeight;
        }
        $additionalPackagePoundsWeight = floor($packageOuncesWeight / self::OUNCES_POUND);
        $packagePoundsWeight += $additionalPackagePoundsWeight;
        $packageOuncesWeight -= $additionalPackagePoundsWeight * self::OUNCES_POUND;
        if ($packagePoundsWeight + $packageOuncesWeight / self::OUNCES_POUND < $packageWeight) {
            list($packagePoundsWeight, $packageOuncesWeight) = $this->_convertPoundOunces($packageWeight);
        }

        $xml->addChild('GrossPounds', $packagePoundsWeight);
        $xml->addChild('GrossOunces', $packageOuncesWeight);
        if ($packageParams->getContentType() == 'OTHER' && $packageParams->getContentTypeOther() != null) {
            $xml->addChild('ContentType', $packageParams->getContentType());
            $xml->addChild('ContentTypeOther ', $packageParams->getContentTypeOther());
        } else {
            $xml->addChild('ContentType', $packageParams->getContentType());
        }

        $xml->addChild('Agreement', 'y');
        $xml->addChild('ImageType', 'PDF');
        $xml->addChild('ImageLayout', 'ALLINONEFILE');
        if ($method == 'FirstClass') {
            $xml->addChild('Container', $container);
        }
        // set size
        if ($packageParams->getSize()) {
            $xml->addChild('Size', $packageParams->getSize());
        }
        // set dimensions
        $xml->addChild('Length', $length);
        $xml->addChild('Width', $width);
        $xml->addChild('Height', $height);
        if ($girth) {
            $xml->addChild('Girth', $girth);
        }

        $xml = $xmlWrap->{$rootNode}->asXML();

        return $xml;
    }
    
    /**
     * Get tracking
     *
     * @param string|string[] $trackings
     * @return Result
     */
    public function getTrackingVendor($trackings,$vendorId)
    {
    
        $this->setTrackingReqeust();

        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }

        $this->_getXmlTrackingVendor($trackings,$vendorId);

        return $this->_result;
    }
    
    /**
     * Send request for tracking
     *
     * @param string[] $trackings
     * @return void
     */
    protected function _getXmlTrackingVendor($trackings,$vendorId)
    {

        $r = $this->_rawTrackRequest;

        $userId = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData('userid',$vendorId)
            : $this->getConfigData('userid');

        $password = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData('password',$vendorId)
            : $this->getConfigData('password');

        foreach ($trackings as $tracking) {
            $xml = $this->_xmlElFactory->create(
                ['data' => '<?xml version = "1.0" encoding = "UTF-8"?><TrackRequest/>']
            );
            $xml->addAttribute('USERID', $userId);

            $trackid = $xml->addChild('TrackID');
            $trackid->addAttribute('ID', $tracking);

            $api = 'TrackV2';
            $request = $xml->asXML();
            $debugData = ['request' => $request];

            try {
                $url = $this->getConfigData('gateway_url');
                if (!$url) {
                    $url = $this->_defaultGatewayUrl;
                }
                $client = $this->_httpClientFactory->create();
                $client->setUri($url);
                $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
                $client->setParameterGet('API', $api);
                $client->setParameterGet('XML', $request);
                $response = $client->request();
                $responseBody = $response->getBody();
                $debugData['result'] = $responseBody;
            } catch (\Exception $e) {
                $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
                $responseBody = '';
            }

            $this->_debug($debugData);
            $this->_parseXmlTrackingResponse($tracking, $responseBody);
        }
    }
    
    /**
     * Get tracking information
     *
     * @param string $tracking
     * @return string|false
     * @api
     */
    public function getVendorTrackingInfo($tracking,$vendorId)
    {

        $result = $this->getTrackingVendor($tracking,$vendorId);

        if ($result instanceof \Magento\Shipping\Model\Tracking\Result) {
            $trackings = $result->getAllTrackings();
            if ($trackings) {
                return $trackings[0];
            }
        } elseif (is_string($result) && !empty($result)) {
            return $result;
        }

        return false;
    }
}