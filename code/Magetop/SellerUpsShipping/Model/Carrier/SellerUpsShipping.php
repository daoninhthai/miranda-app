<?php
/**
 * Copyright © 2013-2020 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magetop\SellerUpsShipping\Model\Carrier;

use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Module\Dir;
use Magento\Sales\Model\Order\Shipment;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Rate\Result;
use Magento\Framework\Xml\Security;
use Laminas\Http\Client;
use Magento\Framework\Measure\Weight;
use Magento\Framework\Measure\Length;

/**
 * DHL International (API v1.4)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class SellerUpsShipping extends \Magento\Ups\Model\Carrier
{
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
        $config = $om->create('Magetop\SellerUpsShipping\Model\SellerUpsShipping')->getCollection()->addFieldToFilter('seller_id',$vendorId)->getFirstItem();
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
     * Get cgi rates for UPS Type : United Parcel Service
     *
     * @return Result
     */
    protected function _getCgiQuotes()
    {
        $rowRequest = $this->_rawRequest;
        $this->_result = $this->_rateFactory->create();
        // make separate request for Smart Post method
        $quotes = $this->groupItemsByVendor();
        foreach($quotes as $vendorId=>$items) {
            if(!$this->getVendorConfigData("enable",$vendorId)) continue;  
            $checkCountryAllow = $this->checkAvailableShipCountriesForVendor($rowRequest,$vendorId);  
            if (false == $checkCountryAllow || $checkCountryAllow instanceof \Magento\Quote\Model\Quote\Address\RateResult\Error) {
                continue;
            }
                    
            $seller = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magetop\Marketplace\Model\Sellers')->getCollection()->addFieldToFilter('user_id',$vendorId)->getFirstItem();
            $vendor = $seller->getData();

            $weight = 0;
            $amount = 0;
            foreach($items as $item){
                $weight+= $item->getWeight()*$item->getQty();
                $amount+= $item->getBaseRowTotal();
            }
            $weight = $this->getTotalNumOfBoxes($weight);

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

            $pickup = $pickup = $this->getAdminConfigData('carriers/ups/api_type')=='seller' ?
                $this->getVendorConfigData('pickup',$vendorId)
                : $this->getConfigData('pickup');

            if (self::USA_COUNTRY_ID == $rowRequest->getDestCountry()) {
                $destPostal = substr($rowRequest->getDestPostal(), 0, 5);
            } else {
                $destPostal = $rowRequest->getDestPostal();
            }
            $pickup = $this->configHelper->getCode('pickup', $pickup);

            $params = [
                'accept_UPS_license_agreement' => 'yes',
                '10_action' => $rowRequest->getAction(),
                '13_product' => $rowRequest->getProduct(),
                '14_origCountry' => $vendor['country'],
                '15_origPostal' => $vendor['zipcode'],
                'origCity' => $vendor['city'],
                '19_destPostal' => $destPostal,
                '22_destCountry' => $rowRequest->getDestCountry(),
                '23_weight' => $weight,
                '47_rate_chart' => $pickup,
                '48_container' => $rowRequest->getContainer(),
                '49_residential' => $rowRequest->getDestType(),
                'weight_std' => strtolower($rowRequest->getUnitMeasure()),
            ];
            $params['47_rate_chart'] = $params['47_rate_chart']['label'];

            $responseBody = $this->_getCachedQuotes($params);
            if ($responseBody === null) {
                $debugData = ['request' => $params];
                try {
                    $url = $this->getConfigData('gateway_url');
                    if (!$url) {
                        $url = $this->_defaultCgiGatewayUrl;
                    }
                    $client = new Client();
                    $client->setUri($url);
                    $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
                    $client->setParameterGet($params);
                    $response = $client->request();
                    $responseBody = $response->getBody();

                    $debugData['result'] = $responseBody;
                    $this->_setCachedQuotes($params, $responseBody);
                } catch (\Exception $e) {
                    $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
                    $responseBody = '';
                }
                $this->_debug($debugData);
            }
            $preparedGeneral = $this->_parseCgiResponseVendor($responseBody,$vendorRequest);
            if (!$preparedGeneral->getError()) {
                $this->_result->append($preparedGeneral);
            }

        }
        return $this->_result;

    }

    /**
     * Prepare shipping rate result based on response
     *
     * @param string $response
     * @return Result
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _parseCgiResponseVendor($response,$vendorRequest)
    {
        $methods = $this->getAdminConfigData('carriers/ups/api_type')=='seller'?
                    $this->getVendorConfigData('allowed_methods',$vendorRequest->getData("vendor_id")):
                    $this->getConfigData('allowed_methods');
        $allowedMethods = explode(',', $methods??'');
        $costArr = [];
        $priceArr = [];
        if (strlen(trim($response)) > 0) {
            $rRows = explode("\n", $response??'');
            foreach ($rRows as $rRow) {
                $row = explode('%', $rRow??'');
                switch (substr($row[0], -1)) {
                    case 3:
                    case 4:
                        if (in_array($row[1], $allowedMethods)) {
                            $responsePrice = $this->_localeFormat->getNumber($row[8]);
                            $costArr[$row[1]] = $responsePrice;
                            $priceArr[$row[1]] = $this->getMethodPriceVendor($vendorRequest,$responsePrice, $row[1]);
                        }
                        break;
                    case 5:
                        $errorTitle = $row[1];
                        $message = __(
                            'Sorry, something went wrong. Please try again or contact us and we\'ll try to help.'
                        );
                        $this->_logger->debug($message . ': ' . $errorTitle);
                        break;
                    case 6:
                        if (in_array($row[3], $allowedMethods)) {
                            $responsePrice = $this->_localeFormat->getNumber($row[10]);
                            $costArr[$row[3]] = $responsePrice;
                            $priceArr[$row[3]] = $this->getMethodPriceVendor($vendorRequest,$responsePrice, $row[3]);
                        }
                        break;
                    default:
                        break;
                }
            }
            asort($priceArr);
        }

        $result = $this->_rateFactory->create();

        if (empty($priceArr)) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier('ups');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            foreach ($priceArr as $method => $price) {
                $rate = $this->_rateMethodFactory->create();
                $rate->setCarrier('ups');
                $rate->setCarrierTitle($this->getConfigData('title').' by seller : '.$vendorRequest->getData("name"));
                $rate->setMethod($method.'||'.$vendorRequest->getData("vendor_id"));
                $methodArray = $this->configHelper->getCode('method', $method);
                $rate->setMethodTitle($methodArray);
                $rate->setCost($costArr[$method]);
                $rate->setVendorId($vendorRequest->getData("vendor_id"));
                $rate->setPrice($price);
                $result->append($rate);
            }
        }

        return $result;
    }

    /**
     * Form XML for shipment request
     *
     * @param \Magento\Framework\DataObject $request
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _formShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $packageParams = $request->getPackageParams();
        $height = $packageParams->getHeight();
        $width = $packageParams->getWidth();
        $length = $packageParams->getLength();
        $weightUnits = $packageParams->getWeightUnits() == Weight::POUND ? 'LBS' : 'KGS';
        $dimensionsUnits = $packageParams->getDimensionUnits() == Length::INCH ? 'IN' : 'CM';

        $itemsDesc = [];
        $itemsShipment = $request->getPackageItems();
        foreach ($itemsShipment as $itemShipment) {
            $item = new \Magento\Framework\DataObject();
            $item->setData($itemShipment);
            $itemsDesc[] = $item->getName();
        }

        $xmlRequest = $this->_xmlElFactory->create(
            ['data' => '<?xml version = "1.0" ?><ShipmentConfirmRequest xml:lang="en-US"/>']
        );
        $requestPart = $xmlRequest->addChild('Request');
        $requestPart->addChild('RequestAction', 'ShipConfirm');
        $requestPart->addChild('RequestOption', 'nonvalidate');

        $shipmentPart = $xmlRequest->addChild('Shipment');
        if ($request->getIsReturn()) {
            $returnPart = $shipmentPart->addChild('ReturnService');
            // UPS Print Return Label
            $returnPart->addChild('Code', '9');
        }
        $shipmentPart->addChild('Description', substr(implode(' ', $itemsDesc), 0, 35));
        //empirical

        $shipperNumber = $this->getAdminConfigData('carriers/ups/api_type')=='seller'?
                        $this->getVendorConfigData('shipper_number',$request->getVendorId()):
                        $this->getConfigData('shipper_number');

        $shipperPart = $shipmentPart->addChild('Shipper');
        if ($request->getIsReturn()) {
            $shipperPart->addChild('Name', $request->getRecipientContactCompanyName());
            $shipperPart->addChild('AttentionName', $request->getRecipientContactPersonName());
            $shipperPart->addChild('ShipperNumber', $shipperNumber);
            $shipperPart->addChild('PhoneNumber', $request->getRecipientContactPhoneNumber());

            $addressPart = $shipperPart->addChild('Address');
            $addressPart->addChild('AddressLine1', $request->getRecipientAddressStreet());
            $addressPart->addChild('AddressLine2', $request->getRecipientAddressStreet2());
            $addressPart->addChild('City', $request->getRecipientAddressCity());
            $addressPart->addChild('CountryCode', $request->getRecipientAddressCountryCode());
            $addressPart->addChild('PostalCode', $request->getRecipientAddressPostalCode());
            if ($request->getRecipientAddressStateOrProvinceCode()) {
                $addressPart->addChild('StateProvinceCode', $request->getRecipientAddressStateOrProvinceCode());
            }
        } else {
            $shipperPart->addChild('Name', $request->getShipperContactCompanyName());
            $shipperPart->addChild('AttentionName', $request->getShipperContactPersonName());
            $shipperPart->addChild('ShipperNumber', $shipperNumber);
            $shipperPart->addChild('PhoneNumber', $request->getShipperContactPhoneNumber());

            $addressPart = $shipperPart->addChild('Address');
            $addressPart->addChild('AddressLine1', $request->getShipperAddressStreet());
            $addressPart->addChild('AddressLine2', $request->getShipperAddressStreet2());
            $addressPart->addChild('City', $request->getShipperAddressCity());
            $addressPart->addChild('CountryCode', $request->getShipperAddressCountryCode());
            $addressPart->addChild('PostalCode', $request->getShipperAddressPostalCode());
            if ($request->getShipperAddressStateOrProvinceCode()) {
                $addressPart->addChild('StateProvinceCode', $request->getShipperAddressStateOrProvinceCode());
            }
        }

        $shipToPart = $shipmentPart->addChild('ShipTo');
        $shipToPart->addChild('AttentionName', $request->getRecipientContactPersonName());
        $shipToPart->addChild(
            'CompanyName',
            $request->getRecipientContactCompanyName() ? $request->getRecipientContactCompanyName() : 'N/A'
        );
        $shipToPart->addChild('PhoneNumber', $request->getRecipientContactPhoneNumber());

        $addressPart = $shipToPart->addChild('Address');
        $addressPart->addChild('AddressLine1', $request->getRecipientAddressStreet1());
        $addressPart->addChild('AddressLine2', $request->getRecipientAddressStreet2());
        $addressPart->addChild('City', $request->getRecipientAddressCity());
        $addressPart->addChild('CountryCode', $request->getRecipientAddressCountryCode());
        $addressPart->addChild('PostalCode', $request->getRecipientAddressPostalCode());
        if ($request->getRecipientAddressStateOrProvinceCode()) {
            $addressPart->addChild('StateProvinceCode', $request->getRecipientAddressRegionCode());
        }
        if ($this->getConfigData('dest_type') == 'RES') {
            $addressPart->addChild('ResidentialAddress');
        }

        if ($request->getIsReturn()) {
            $shipFromPart = $shipmentPart->addChild('ShipFrom');
            $shipFromPart->addChild('AttentionName', $request->getShipperContactPersonName());
            $shipFromPart->addChild(
                'CompanyName',
                $request->getShipperContactCompanyName() ? $request
                    ->getShipperContactCompanyName() : $request
                    ->getShipperContactPersonName()
            );
            $shipFromAddress = $shipFromPart->addChild('Address');
            $shipFromAddress->addChild('AddressLine1', $request->getShipperAddressStreet1());
            $shipFromAddress->addChild('AddressLine2', $request->getShipperAddressStreet2());
            $shipFromAddress->addChild('City', $request->getShipperAddressCity());
            $shipFromAddress->addChild('CountryCode', $request->getShipperAddressCountryCode());
            $shipFromAddress->addChild('PostalCode', $request->getShipperAddressPostalCode());
            if ($request->getShipperAddressStateOrProvinceCode()) {
                $shipFromAddress->addChild('StateProvinceCode', $request->getShipperAddressStateOrProvinceCode());
            }

            $addressPart = $shipToPart->addChild('Address');
            $addressPart->addChild('AddressLine1', $request->getShipperAddressStreet1());
            $addressPart->addChild('AddressLine2', $request->getShipperAddressStreet2());
            $addressPart->addChild('City', $request->getShipperAddressCity());
            $addressPart->addChild('CountryCode', $request->getShipperAddressCountryCode());
            $addressPart->addChild('PostalCode', $request->getShipperAddressPostalCode());
            if ($request->getShipperAddressStateOrProvinceCode()) {
                $addressPart->addChild('StateProvinceCode', $request->getShipperAddressStateOrProvinceCode());
            }
            if ($this->getConfigData('dest_type') == 'RES') {
                $addressPart->addChild('ResidentialAddress');
            }
        }

        $servicePart = $shipmentPart->addChild('Service');
        $servicePart->addChild('Code', $request->getShippingMethod());
        $packagePart = $shipmentPart->addChild('Package');
        $packagePart->addChild('Description', substr(implode(' ', $itemsDesc), 0, 35));
        //empirical
        $packagePart->addChild('PackagingType')->addChild('Code', $request->getPackagingType());
        $packageWeight = $packagePart->addChild('PackageWeight');
        $packageWeight->addChild('Weight', $request->getPackageWeight());
        $packageWeight->addChild('UnitOfMeasurement')->addChild('Code', $weightUnits);

        // set dimensions
        if ($length || $width || $height) {
            $packageDimensions = $packagePart->addChild('Dimensions');
            $packageDimensions->addChild('UnitOfMeasurement')->addChild('Code', $dimensionsUnits);
            $packageDimensions->addChild('Length', $length);
            $packageDimensions->addChild('Width', $width);
            $packageDimensions->addChild('Height', $height);
        }

        // ups support reference number only for domestic service
        if ($this->_isUSCountry(
                $request->getRecipientAddressCountryCode()
            ) && $this->_isUSCountry(
                $request->getShipperAddressCountryCode()
            )
        ) {
            if ($request->getReferenceData()) {
                $referenceData = $request->getReferenceData() . $request->getPackageId();
            } else {
                $referenceData = 'Order #' .
                    $request->getOrderShipment()->getOrder()->getIncrementId() .
                    ' P' .
                    $request->getPackageId();
            }
            $referencePart = $packagePart->addChild('ReferenceNumber');
            $referencePart->addChild('Code', '02');
            $referencePart->addChild('Value', $referenceData);
        }

        $deliveryConfirmation = $packageParams->getDeliveryConfirmation();
        if ($deliveryConfirmation) {
            /** @var $serviceOptionsNode Element */
            $serviceOptionsNode = null;
            switch ($this->_getDeliveryConfirmationLevel($request->getRecipientAddressCountryCode())) {
                case self::DELIVERY_CONFIRMATION_PACKAGE:
                    $serviceOptionsNode = $packagePart->addChild('PackageServiceOptions');
                    break;
                case self::DELIVERY_CONFIRMATION_SHIPMENT:
                    $serviceOptionsNode = $shipmentPart->addChild('ShipmentServiceOptions');
                    break;
                default:
                    break;
            }
            if (!is_null($serviceOptionsNode)) {
                $serviceOptionsNode->addChild(
                    'DeliveryConfirmation'
                )->addChild(
                    'DCISType',
                    $packageParams->getDeliveryConfirmation()
                );
            }
        }

        $shipmentPart->addChild(
            'PaymentInformation'
        )->addChild(
            'Prepaid'
        )->addChild(
            'BillShipper'
        )->addChild(
            'AccountNumber',
            $shipperNumber
        );

        if ($request->getPackagingType() != $this->configHelper->getCode(
                'container',
                'ULE'
            ) &&
            $request->getShipperAddressCountryCode() == self::USA_COUNTRY_ID &&
            ($request->getRecipientAddressCountryCode() == 'CA' ||
                $request->getRecipientAddressCountryCode() == 'PR')
        ) {
            $invoiceLineTotalPart = $shipmentPart->addChild('InvoiceLineTotal');
            $invoiceLineTotalPart->addChild('CurrencyCode', $request->getBaseCurrencyCode());
            $invoiceLineTotalPart->addChild('MonetaryValue', ceil($packageParams->getCustomsValue()));
        }

        $labelPart = $xmlRequest->addChild('LabelSpecification');
        $labelPart->addChild('LabelPrintMethod')->addChild('Code', 'GIF');
        $labelPart->addChild('LabelImageFormat')->addChild('Code', 'GIF');

        $this->setXMLVendorAccessRequest($request->getVendorId());
        $xmlRequest = $this->_xmlAccessRequest . $xmlRequest->asXml();

        return $xmlRequest;
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
        $configFreeMethod = $this->getAdminConfigData('carriers/ups/api_type')=='seller' ?
            $this->getVendorConfigData('free_method',$vendorRequest->getData("vendor_id"))
            : $this->getConfigData('free_method');

        $freeShippingEnable =  $this->getAdminConfigData('carriers/ups/api_type')=='seller' ?
            $this->getVendorConfigData("free_shipping_enable",$vendorRequest->getData("vendor_id"))
            : $this->getConfigData("free_shipping_enable");

        $freeShippingSubtotal=  $this->getAdminConfigData('carriers/ups/api_type')=='seller' ?
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
        $speCountriesAllow = $this->getAdminConfigData('carriers/ups/api_type')=='seller' ?
            $this->getVendorConfigData('sallowspecific',$vendorId)
            : $this->getConfigData('sallowspecific');

        /*
         * for specific countries, the flag will be 1
         */
        if ($speCountriesAllow && $speCountriesAllow == 1) {

            $showMethod = $this->getConfigData('showmethod');
            $availableCountries = [];

            $specificcountry = $this->getAdminConfigData('carriers/ups/api_type')=='seller' ?
                $this->getVendorConfigData('specificcountry',$vendorId)
                : $this->getConfigData('specificcountry');

            if ($specificcountry) {
                $availableCountries = explode(',', $specificcountry??'');
            }
            if ($availableCountries && in_array($request->getDestCountry(), $availableCountries)) {
                return $this;
            } elseif ($showMethod && (!$availableCountries || $availableCountries && !in_array(
                        $request->getDestCountry(),
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
     * Set xml access request
     *
     * @return void
     */
    protected function setXMLVendorAccessRequest($vendorId)
    {
        $userId = $this->getAdminConfigData('carriers/ups/api_type')=='seller' ?
            $this->getVendorConfigData('username',$vendorId)
            : $this->getConfigData('username');

        $userIdPass = $this->getAdminConfigData('carriers/ups/api_type')=='seller' ?
            $this->getVendorConfigData('password',$vendorId)
            : $this->getConfigData('password');

        $accessKey = $this->getAdminConfigData('carriers/ups/api_type')=='seller' ?
            $this->getVendorConfigData('access_license_number',$vendorId)
            : $this->getConfigData('access_license_number');

        $this->_xmlAccessRequest = <<<XMLAuth
<?xml version="1.0"?>
<AccessRequest xml:lang="en-US">
  <AccessLicenseNumber>$accessKey</AccessLicenseNumber>
  <UserId>$userId</UserId>
  <Password>$userIdPass</Password>
</AccessRequest>
XMLAuth;
    }

    /**
     * Get tracking
     *
     * @param string|string[] $trackings
     * @return Result
     */
    public function getTrackingVendor($trackings,$vendorId)
    {
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }

        if ($this->getConfigData('type') == 'UPS') {
            $this->_getCgiTracking($trackings);
        } elseif ($this->getConfigData('type') == 'UPS_XML') {
            $this->setXMLVendorAccessRequest($vendorId);
            $this->_getXmlTracking($trackings);
        }

        return $this->_result;
    }

    /**
     * Get xml rates for UPS Type : United Parcel Service XML
     *
     * @return Result
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _getXmlQuotes()
    {
        $rowRequest = $this->_rawRequest;
        $url = $this->getConfigData('gateway_xml_url');
        $this->_result = $this->_rateFactory->create();
        // make separate request for Smart Post method
        $quotes = $this->groupItemsByVendor();
        $this->setXMLAccessRequest();
        $xmlRequest = $this->_xmlAccessRequest;
        $debugData['accessRequest'] = $this->filterDebugData($xmlRequest);

        foreach($quotes as $vendorId=>$items) {
            if (!$this->getVendorConfigData("enable", $vendorId)) continue;
            $checkCountryAllow = $this->checkAvailableShipCountriesForVendor($rowRequest, $vendorId);
            if (false == $checkCountryAllow || $checkCountryAllow instanceof \Magento\Quote\Model\Quote\Address\RateResult\Error) {
                continue;
            }
            
            $seller = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magetop\Marketplace\Model\Sellers')->getCollection()->addFieldToFilter('user_id',$vendorId)->getFirstItem();
            $vendor = $seller->getData();

            $weight = 0;
            $amount = 0;
            foreach ($items as $item) {
                $weight += $item->getWeight() * $item->getQty();
                $amount += $item->getBaseRowTotal();
            }
            $weight = $this->getTotalNumOfBoxes($weight);

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

            $rowRequest = $this->_rawRequest;
            if (self::USA_COUNTRY_ID == $rowRequest->getDestCountry()) {
                $destPostal = substr($rowRequest->getDestPostal(), 0, 5);
            } else {
                $destPostal = $rowRequest->getDestPostal();
            }

            $pickup = $this->getAdminConfigData('carriers/ups/api_type')=='seller' ?
                $this->getVendorConfigData('pickup',$vendorId)
                : $this->getConfigData('pickup');

            $shipper = $this->getAdminConfigData('carriers/ups/api_type')=='seller' ?
                $this->getVendorConfigData('shipper_number',$vendorId)
                : $this->getConfigData('shipper_number');

            $negotiated_active = $this->getAdminConfigData('carriers/ups/api_type')=='seller' ?
                $this->getVendorConfigData('negotiated_active',$vendorId)
                : $this->getConfigData('negotiated_active');
                                
            $pickup = $this->configHelper->getCode('pickup', $pickup);
            $params = [
                'accept_UPS_license_agreement' => 'yes',
                '10_action' => $rowRequest->getAction(),
                '13_product' => $rowRequest->getProduct(),
                '14_origCountry' => $vendor['country'],
                '15_origPostal' => $vendor['zipcode'],
                'origCity' => $vendor['city'],
                'origRegionCode' => $vendor['state'],
                '19_destPostal' => $destPostal,
                '22_destCountry' => $rowRequest->getDestCountry(),
                'destRegionCode' => $rowRequest->getDestRegionCode(),
                '23_weight' => $weight,
                '47_rate_chart' => $pickup,
                '48_container' => $rowRequest->getContainer(),
                '49_residential' => $rowRequest->getDestType(),
            ];

            if ($params['10_action'] == '4') {
                $params['10_action'] = 'Shop';
                $serviceCode = null;
            } else {
                $params['10_action'] = 'Rate';
                $serviceCode = $rowRequest->getProduct() ? $rowRequest->getProduct() : '';
            }
            $serviceDescription = $serviceCode ? $this->getVendorShipmentByCode($vendorId,$serviceCode) : '';

            $xmlParams = <<<XMLRequest
<?xml version="1.0"?>
<RatingServiceSelectionRequest xml:lang="en-US">
  <Request>
    <TransactionReference>
      <CustomerContext>Rating and Service</CustomerContext>
      <XpciVersion>1.0</XpciVersion>
    </TransactionReference>
    <RequestAction>Rate</RequestAction>
    <RequestOption>{$params['10_action']}</RequestOption>
  </Request>
  <PickupType>
          <Code>{$params['47_rate_chart']['code']}</Code>
          <Description>{$params['47_rate_chart']['label']}</Description>
  </PickupType>

  <Shipment>
XMLRequest;

            if ($serviceCode !== null) {
                $xmlParams .= "<Service>" .
                    "<Code>{$serviceCode}</Code>" .
                    "<Description>{$serviceDescription}</Description>" .
                    "</Service>";
            }

            $xmlParams .= <<<XMLRequest
      <Shipper>
XMLRequest;

            if ($negotiated_active && $shipper) {
                $xmlParams .= "<ShipperNumber>{$shipper}</ShipperNumber>";
            }

            if ($rowRequest->getIsReturn()) {
                $shipperCity = '';
                $shipperPostalCode = $params['19_destPostal'];
                $shipperCountryCode = $params['22_destCountry'];
                $shipperStateProvince = $params['destRegionCode'];
            } else {
                $shipperCity = $params['origCity'];
                $shipperPostalCode = $params['15_origPostal'];
                $shipperCountryCode = $params['14_origCountry'];
                $shipperStateProvince = $params['origRegionCode'];
            }

            $xmlParams .= <<<XMLRequest
      <Address>
          <City>{$shipperCity}</City>
          <PostalCode>{$shipperPostalCode}</PostalCode>
          <CountryCode>{$shipperCountryCode}</CountryCode>
          <StateProvinceCode>{$shipperStateProvince}</StateProvinceCode>
      </Address>
    </Shipper>
    <ShipTo>
      <Address>
          <PostalCode>{$params['19_destPostal']}</PostalCode>
          <CountryCode>{$params['22_destCountry']}</CountryCode>
          <ResidentialAddress>{$params['49_residential']}</ResidentialAddress>
          <StateProvinceCode>{$params['destRegionCode']}</StateProvinceCode>
XMLRequest;

            if ($params['49_residential'] === '01') {
                $xmlParams .= "<ResidentialAddressIndicator>{$params['49_residential']}</ResidentialAddressIndicator>";
            }

            $xmlParams .= <<<XMLRequest
      </Address>
    </ShipTo>


    <ShipFrom>
      <Address>
          <PostalCode>{$params['15_origPostal']}</PostalCode>
          <CountryCode>{$params['14_origCountry']}</CountryCode>
          <StateProvinceCode>{$params['origRegionCode']}</StateProvinceCode>
      </Address>
    </ShipFrom>

    <Package>
      <PackagingType><Code>{$params['48_container']}</Code></PackagingType>
      <PackageWeight>
         <UnitOfMeasurement><Code>{$rowRequest->getUnitMeasure()}</Code></UnitOfMeasurement>
        <Weight>{$params['23_weight']}</Weight>
      </PackageWeight>
    </Package>
XMLRequest;

            if ($negotiated_active) {
                $xmlParams .= "<RateInformation><NegotiatedRatesIndicator/></RateInformation>";
            }

            $xmlParams .= <<<XMLRequest
  </Shipment>
</RatingServiceSelectionRequest>
XMLRequest;

            $xmlRequest .= $xmlParams;

            $xmlResponse = $this->_getCachedQuotes($xmlRequest);
            if ($xmlResponse === null) {
                $debugData['request'] = $xmlParams;
                try {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt($ch, CURLOPT_HEADER, 0);
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $xmlRequest);
                    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, (bool)$this->getConfigFlag('mode_xml'));
                    $xmlResponse = curl_exec($ch);

                    $debugData['result'] = $xmlResponse;
                    $this->_setCachedQuotes($xmlRequest, $xmlResponse);
                } catch (\Exception $e) {
                    $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
                    $xmlResponse = '';
                }
                $this->_debug($debugData);
            }
            $preparedGeneral = $this->_parseXmlResponseVendor($xmlResponse,$vendorRequest);
            if (!$preparedGeneral->getError()) {
                $this->_result->append($preparedGeneral);
            }
        }

        return $this->_result ;
    }


    /**
     * Prepare shipping rate result based on response
     *
     * @param mixed $xmlResponse
     * @return Result
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _parseXmlResponseVendor($xmlResponse,$vendorRequest)
    {
        $shipper = $this->getAdminConfigData('carriers/ups/api_type')=='seller'?
            $this->getVendorConfigData('shipper_number',$vendorRequest->getData("vendor_id")):
            $this->getConfigData('shipper_number');
        $negotiated_active = $this->getAdminConfigData('carriers/ups/api_type')=='seller'?
            $this->getVendorConfigData('negotiated_active',$vendorRequest->getData("vendor_id")):
            $this->getConfigData('negotiated_active');
        $methods = $this->getAdminConfigData('carriers/ups/api_type')=='seller'?
            $this->getVendorConfigData('allowed_methods',$vendorRequest->getData("vendor_id")):
            $this->getConfigData('allowed_methods');
        $allowedMethods = explode(',', $methods??'');

        $costArr = [];
        $priceArr = [];
        if (strlen(trim($xmlResponse)) > 0) {
            $xml = new \Magento\Framework\Simplexml\Config();
            $xml->loadString($xmlResponse);
            $arr = $xml->getXpath("//RatingServiceSelectionResponse/Response/ResponseStatusCode/text()");
            $success = (int)$arr[0];
            if ($success === 1) {
                $arr = $xml->getXpath("//RatingServiceSelectionResponse/RatedShipment");

                // Negotiated rates
                $negotiatedArr = $xml->getXpath("//RatingServiceSelectionResponse/RatedShipment/NegotiatedRates");
                $negotiatedActive = $negotiated_active && $shipper && !empty($negotiatedArr);

                $allowedCurrencies = $this->_currencyFactory->create()->getConfigAllowCurrencies();
                foreach ($arr as $shipElement) {
                    $code = (string)$shipElement->Service->Code;
                    if (in_array($code, $allowedMethods)) {
                        if ($negotiatedActive) {
                            $cost = $shipElement->NegotiatedRates->NetSummaryCharges->GrandTotal->MonetaryValue;
                        } else {
                            $cost = $shipElement->TotalCharges->MonetaryValue;
                        }

                        //convert price with Origin country currency code to base currency code
                        $successConversion = true;
                        $responseCurrencyCode = $this->mapCurrencyCode(
                            (string)$shipElement->TotalCharges->CurrencyCode
                        );
                        if ($responseCurrencyCode) {
                            if (in_array($responseCurrencyCode, $allowedCurrencies)) {
                                $cost = (double)$cost * $this->_getBaseCurrencyRate($responseCurrencyCode);
                            } else {
                                $errorTitle = __(
                                    'We can\'t convert a rate from "%1-%2".',
                                    $responseCurrencyCode,
                                    $this->_request->getPackageCurrency()->getCode()
                                );
                                $error = $this->_rateErrorFactory->create();
                                $error->setCarrier('ups');
                                $error->setCarrierTitle($this->getConfigData('title'));
                                $error->setErrorMessage($errorTitle);
                                $successConversion = false;
                            }
                        }

                        if ($successConversion) {
                            $costArr[$code] = $cost;
                            $priceArr[$code] = $this->getMethodPriceVendor($vendorRequest,floatval($cost), $code);
                        }
                    }
                }
            } else {
                $arr = $xml->getXpath("//RatingServiceSelectionResponse/Response/Error/ErrorDescription/text()");
                $errorTitle = (string)$arr[0][0];
                $error = $this->_rateErrorFactory->create();
                $error->setCarrier('ups');
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            }
        }

        $result = $this->_rateFactory->create();

        if (empty($priceArr)) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier('ups');
            $error->setCarrierTitle($this->getConfigData('title'));
            if (!isset($errorTitle)) {
                $errorTitle = __('Cannot retrieve shipping rates');
            }
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            foreach ($priceArr as $method => $price) {
                $rate = $this->_rateMethodFactory->create();
                $rate->setCarrier('ups');
                $rate->setCarrierTitle($this->getConfigData('title').' by seller : '.$vendorRequest->getData("name"));
                $rate->setMethod($method.'||'.$vendorRequest->getData("vendor_id"));
                $methodArr = $this->getVendorShipmentByCode($vendorRequest->getData("vendor_id"),$method);
                $rate->setMethodTitle($methodArr);
                $rate->setCost($costArr[$method]);
                $rate->setVendorId($vendorRequest->getData("vendor_id"));
                $rate->setPrice($price);
                $result->append($rate);
            }
        }

        return $result;
    }

    /**
     * Get shipment by code
     *
     * @param string $code
     * @param string $origin
     * @return array|bool
     */
    public function getVendorShipmentByCode($vendorId , $code, $origin = null)
    {

        $origin_shipment = $this->getAdminConfigData('carriers/ups/api_type')=='seller'?
            $this->getVendorConfigData('origin_shipment',$vendorId):
            $this->getConfigData('origin_shipment');

        if ($origin === null) {
            $origin = $origin_shipment;
        }
        $arr = $this->configHelper->getCode('originShipment', $origin);
        if (isset($arr[$code])) {
            return $arr[$code];
        } else {
            return false;
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