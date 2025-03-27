<?php
/**
 * Copyright � 2013-2020 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magetop\SellerFedExShipping\Model\Carrier;

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
class SellerFedExShipping extends \Magento\Fedex\Model\Carrier
{
    /**
     * Version of tracking service
     * @var int
     */
    private static $trackServiceVersion = 10;

    /**
     * List of TrackReply errors
     * @var array
     */
    private static $trackingErrors = ['FAILURE', 'ERROR'];

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
        $config = $om->create('Magetop\SellerFedExShipping\Model\SellerFedExShipping')->getCollection()->addFieldToFilter('seller_id',$vendorId)->getFirstItem();
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
     * Do remote request for and handle errors
     *
     * @return Result
     */
    protected function _getQuotes()
    {
        try {
            $this->_result = $this->_rateFactory->create();
            // make separate request for Smart Post method


            $rawRequest = $this->_rawRequest;
            $quotes = $this->groupItemsByVendor();

            foreach($quotes as $vendorId=>$items) {
                if(!$this->getVendorConfigData("enable",$vendorId)) continue;

                $checkCountryAllow = $this->checkAvailableShipCountriesForVendor($rawRequest,$vendorId);

                if (false == $checkCountryAllow || $checkCountryAllow instanceof \Magento\Quote\Model\Quote\Address\RateResult\Error) {
                    continue;
                }

                $seller = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magetop\Marketplace\Model\Sellers')->getCollection()->addFieldToFilter('user_id',$vendorId)->getFirstItem();
                $vendor = $seller->getData();

                $methods = $this->getAdminConfigData('carriers/usps/api_type')=='seller'?
                    $this->getVendorConfigData('allowed_methods',$vendorRequest->getData("vendor_id")):
                    $this->getConfigData('allowed_methods');

                $allowedMethods = explode(',', $methods??'');


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

                if (in_array(self::RATE_REQUEST_SMARTPOST, $allowedMethods)) {
                    $response = $this->_doVendorRatesRequest(self::RATE_REQUEST_SMARTPOST,$vendorRequest);

                    $preparedSmartpost = $this->_prepareVendorRateResponse($response,$vendorRequest);
                    if (!$preparedSmartpost->getError()) {
                        $this->_result->append($preparedSmartpost);
                    }
                }
                // make general request for all methods
                $response = $this->_doVendorRatesRequest(self::RATE_REQUEST_GENERAL,$vendorRequest);


                $preparedGeneral = $this->_prepareVendorRateResponse($response,$vendorRequest);
                if (!$preparedGeneral->getError()) {
                    $this->_result->append($preparedGeneral);
                }

            }


            return $this->_result;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }

    /**
     * Makes remote request to the carrier and returns a response
     *
     * @param string $purpose
     * @return mixed
     */
    protected function _doVendorRatesRequest($purpose,$vendorRequest)
    {
        try {
            $ratesRequest = $this->_formVendorRateRequest($purpose,$vendorRequest);


            $requestString = serialize($ratesRequest);


            $response = $this->_getCachedQuotes($requestString);

            $debugData = ['request' => $this->filterDebugData($ratesRequest)];
            if ($response === null) {
                try {
                    $client = $this->_createRateSoapClient();
                    $response = $client->getRates($ratesRequest);
                    $this->_setCachedQuotes($requestString, serialize($response));
                    $debugData['result'] = $response;
                } catch (\Exception $e) {
                    $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
                    $this->_logger->critical($e);
                }
            } else {
                $response = @unserialize($response);
                $debugData['result'] = $response;
            }
            $this->_debug($debugData);

            return $response;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }

    /**
     * Checks whether some request to rates have already been done, so we have cache for it
     * Used to reduce number of same requests done to carrier service during one session
     *
     * Returns cached response or null
     *
     * @param string|array $requestParams
     * @return null|string
     */
    protected function _getCachedQuotes($requestParams)
    {
        $key = $this->_getQuotesCacheKey($requestParams);

        return isset(self::$_quotesCache[$key]) ? self::$_quotesCache[$key] : null;
    }


    /**
     * Forming request for rate estimation depending to the purpose
     *
     * @param string $purpose
     * @return array
     */
    protected function _formVendorRateRequest($purpose,$vendorRequest)
    {
        try {
            $account = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData('account',$vendorRequest->getData("vendor_id"))
                : $this->getConfigData('account');
            $meter_number = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData('meter_number',$vendorRequest->getData("vendor_id"))
                : $this->getConfigData('meter_number');
            $key = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData('key',$vendorRequest->getData("vendor_id"))
                : $this->getConfigData('key');
            $password = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData('password',$vendorRequest->getData("vendor_id"))
                : $this->getConfigData('password');


            $r = $this->_rawRequest;


            $ratesRequest = [
                'WebAuthenticationDetail' => [
                    'UserCredential' => ['Key' => $key, 'Password' => $password],
                ],
                'ClientDetail' => ['AccountNumber' => $account, 'MeterNumber' => $meter_number],
                'Version' => $this->getVersionInfo(),
                'RequestedShipment' => [
                    'DropoffType' => $r->getDropoffType(),
                    'ShipTimestamp' => date('c'),
                    'PackagingType' => $r->getPackaging(),
                    'TotalInsuredValue' => ['Amount' => $r->getValue(), 'Currency' => $this->getCurrencyCode()],
                    'Shipper' => [
                        'Address' => ['PostalCode' => $vendorRequest->getData("postcode"), 'CountryCode' => $vendorRequest->getData("country")],
                    ],
                    'Recipient' => [
                        'Address' => [
                            'PostalCode' => $r->getDestPostal(),
                            'CountryCode' => $r->getDestCountry(),
                            'Residential' => (bool)$this->getConfigData('residence_delivery'),
                        ],
                    ],
                    'ShippingChargesPayment' => [
                        'PaymentType' => 'SENDER',
                        'Payor' => ['AccountNumber' => $account, 'CountryCode' => $vendorRequest->getData("country")],
                    ],
                    'CustomsClearanceDetail' => [
                        'CustomsValue' => ['Amount' => $vendorRequest->getData("amount"), 'Currency' => $this->getCurrencyCode()],
                        'CommercialInvoice' => ['Purpose' => "SOLD"]
                    ],
                    'RateRequestTypes' => 'LIST',
                    'PackageCount' => '1',
                    'PackageDetail' => 'INDIVIDUAL_PACKAGES',
                    'RequestedPackageLineItems' => [
                        '0' => [
                            'Weight' => [
                                'Value' => (double)$vendorRequest->getData("weight"),
                                'Units' => $this->getConfigData('unit_of_measure'),
                            ],
                            'GroupPackageCount' => 1,
                        ],
                    ],
                ],
            ];




            if ($r->getDestCity()) {
                $ratesRequest['RequestedShipment']['Recipient']['Address']['City'] = $r->getDestCity();
            }

            if ($purpose == self::RATE_REQUEST_GENERAL) {
                $ratesRequest['RequestedShipment']['RequestedPackageLineItems'][0]['InsuredValue'] = [
                    'Amount' => $vendorRequest->getData("amount"),
                    'Currency' => $this->getCurrencyCode(),
                ];
            } else {
                if ($purpose == self::RATE_REQUEST_SMARTPOST) {
                    $ratesRequest['RequestedShipment']['ServiceType'] = self::RATE_REQUEST_SMARTPOST;
                    $ratesRequest['RequestedShipment']['SmartPostDetail'] = [
                        'Indicia' => (double)$vendorRequest->getData("weight") >= 1 ? 'PARCEL_SELECT' : 'PRESORTED_STANDARD',
                        'HubId' => $this->getConfigData('smartpost_hubid'),
                    ];
                }
            }

            //var_dump($ratesRequest);exit;

            return $ratesRequest;

        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }

    /**
     * Prepare shipping rate result based on response
     *
     * @param mixed $response
     * @return Result
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _prepareVendorRateResponse($response,$vendorRequest)
    {
        try {
            $costArr = [];
            $priceArr = [];
            $errorTitle = 'For some reason we can\'t retrieve tracking info right now.';


            if (is_object($response)) {
                if ($response->HighestSeverity == 'FAILURE' || $response->HighestSeverity == 'ERROR') {
                    if (is_array($response->Notifications)) {
                        $notification = array_pop($response->Notifications);
                        $errorTitle = (string)$notification->Message;
                    } else {
                        $errorTitle = (string)$response->Notifications->Message;
                    }
                } elseif (isset($response->RateReplyDetails)) {

                    $methods = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData('allowed_methods',$vendorRequest->getData("vendor_id"))
                        : $this->getConfigData('allowed_methods');

                    $allowedMethods = explode(',', $methods??'');


                    if (is_array($response->RateReplyDetails)) {
                        foreach ($response->RateReplyDetails as $rate) {
                            $serviceName = (string)$rate->ServiceType;
                            if (in_array($serviceName, $allowedMethods)) {
                                $amount = $this->_getRateAmountOriginBased($rate);
                                $costArr[$serviceName] = $amount;
                                $priceArr[$serviceName] = $this->getMethodPriceVendor($vendorRequest,$amount, $serviceName);
                            }
                        }
                        asort($priceArr);
                    } else {
                        $rate = $response->RateReplyDetails;
                        $serviceName = (string)$rate->ServiceType;
                        if (in_array($serviceName, $allowedMethods)) {
                            $amount = $this->_getRateAmountOriginBased($rate);
                            $costArr[$serviceName] = $amount;
                            $priceArr[$serviceName] = $this->getMethodPriceVendor($vendorRequest,$amount, $serviceName);
                        }
                    }
                }
            }

            $result = $this->_rateFactory->create();
            if (empty($priceArr)) {
                $error = $this->_rateErrorFactory->create();
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setErrorMessage($errorTitle);
                $error->setErrorMessage($this->getConfigData('specificerrmsg'));
                $result->append($error);
            } else {
                foreach ($priceArr as $method => $price) {
                    $rate = $this->_rateMethodFactory->create();
                    $rate->setCarrier($this->_code);
                    $rate->setCarrierTitle($this->getConfigData('title').' by seller : '.$vendorRequest->getData("name"));
                    $rate->setMethod($method.'||'.$vendorRequest->getData("vendor_id"));
                    $rate->setMethodTitle($this->getCode('method', $method));
                    $rate->setCost($costArr[$method]);
                    $rate->setPrice($price);
                    $rate->setVendorId($vendorRequest->getData("vendor_id"));
                    $result->append($rate);
                }
            }

            return $result;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
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
        try {
        $configFreeMethod = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ?
            $this->getVendorConfigData($this->_freeMethod,$vendorRequest->getData("vendor_id"))
            : $this->getConfigData($this->_freeMethod);

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
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }

    /**
     * @param \Magento\Framework\DataObject $request
     * @return $this|bool|false|\Magento\Framework\Model\AbstractModel
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function checkAvailableShipCountriesForVendor(\Magento\Framework\DataObject $request,$vendorId)
    {
        $speCountriesAllow =  $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData("sallowspecific",$vendorId)
            : $this->getConfigData("sallowspecific");

        /*
         * for specific countries, the flag will be 1
         */
        if ($speCountriesAllow && $speCountriesAllow == 1) {

            $showMethod = $this->getConfigData('showmethod');
            $availableCountries = [];

            $specificcountry = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData("specificcountry",$vendorId)
                : $this->getConfigData("specificcountry");

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
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param \Magento\Framework\DataObject $request
     * @return \Magento\Framework\DataObject
     */
    protected function _doShipmentRequest(\Magento\Framework\DataObject $request) : \Magento\Framework\DataObject
    {
        try {
            $this->_prepareShipmentRequest($request);
            $result = new \Magento\Framework\DataObject();
            $client = $this->_createShipSoapClient();
            $requestClient = $this->_formShipmentRequest($request);
            $response = $client->processShipment($requestClient);


            if ($response->HighestSeverity != 'FAILURE' && $response->HighestSeverity != 'ERROR') {
                $shippingLabelContent = $response->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image;
                $trackingNumber = $this->getVendorTrackingNumber(
                    $response->CompletedShipmentDetail->CompletedPackageDetails->TrackingIds
                );

                $result->setShippingLabelContent($shippingLabelContent);
                $result->setTrackingNumber($trackingNumber);
                $debugData = ['request' => $client->__getLastRequest(), 'result' => $client->__getLastResponse()];
                $this->_debug($debugData);
            } else {
                $debugData = [
                    'request' => $client->__getLastRequest(),
                    'result' => ['error' => '', 'code' => '', 'xml' => $client->__getLastResponse()],
                ];
                if (is_array($response->Notifications)) {
                    foreach ($response->Notifications as $notification) {
                        $debugData['result']['code'] .= $notification->Code . '; ';
                        $debugData['result']['error'] .= $notification->Message . '; ';
                    }
                } else {
                    $debugData['result']['code'] = $response->Notifications->Code . ' ';
                    $debugData['result']['error'] = $response->Notifications->Message . ' ';
                }
                $this->_debug($debugData);
                $result->setErrors($debugData['result']['error']);
            }
            $result->setGatewayResponse($client->__getLastResponse());

            return $result;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }

    /**
     * @param array|object $trackingIds
     * @return string
     */
    protected function getVendorTrackingNumber($trackingIds) {
        return is_array($trackingIds) ? array_map(
            function($val) {
                return $val->TrackingNumber;
            },
            $trackingIds
        ) : $trackingIds->TrackingNumber;
    }

    /**
     * Form array with appropriate structure for shipment request
     *
     * @param \Magento\Framework\DataObject $request
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _formShipmentRequest(\Magento\Framework\DataObject $request)
    {
        try {

            $account = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData('account',$request->getVendorId())
                : $this->getConfigData('account');

            if ($request->getReferenceData()) {
                $referenceData = $request->getReferenceData() . $request->getPackageId();
            } else {
                $referenceData = 'Order #' .
                    $request->getOrderShipment()->getOrder()->getIncrementId() .
                    ' P' .
                    $request->getPackageId();
            }
            $packageParams = $request->getPackageParams();
            $customsValue = $packageParams->getCustomsValue();
            $height = $packageParams->getHeight();
            $width = $packageParams->getWidth();
            $length = $packageParams->getLength();
            $weightUnits = $packageParams->getWeightUnits() == Weight::POUND ? 'LB' : 'KG';
            $unitPrice = 0;
            $itemsQty = 0;
            $itemsDesc = [];
            $countriesOfManufacture = [];
            $productIds = [];
            $packageItems = $request->getPackageItems();
            foreach ($packageItems as $itemShipment) {
                $item = new \Magento\Framework\DataObject();
                $item->setData($itemShipment);

                $unitPrice += $item->getPrice();
                $itemsQty += $item->getQty();

                $itemsDesc[] = $item->getName();
                $productIds[] = $item->getProductId();
            }

            // get countries of manufacture
            $productCollection = $this->_productCollectionFactory->create()->addStoreFilter(
                $request->getStoreId()
            )->addFieldToFilter(
                'entity_id',
                ['in' => $productIds]
            )->addAttributeToSelect(
                'country_of_manufacture'
            );
            foreach ($productCollection as $product) {
                $countriesOfManufacture[] = $product->getCountryOfManufacture();
            }


            $paymentType = $request->getIsReturn() ? 'RECIPIENT' : 'SENDER';
            $optionType = $request->getShippingMethod() == self::RATE_REQUEST_SMARTPOST
                ? 'SERVICE_DEFAULT' : $packageParams->getDeliveryConfirmation();
            $requestClient = [
                'RequestedShipment' => [
                    'ShipTimestamp' => time(),
                    'DropoffType' => $this->getConfigData('dropoff'),
                    'PackagingType' => $request->getPackagingType(),
                    'ServiceType' => $request->getShippingMethod(),
                    'Shipper' => [
                        'Contact' => [
                            'PersonName' => $request->getShipperContactPersonName(),
                            'CompanyName' => $request->getShipperContactCompanyName(),
                            'PhoneNumber' => $request->getShipperContactPhoneNumber(),
                        ],
                        'Address' => [
                            'StreetLines' => [$request->getShipperAddressStreet()],
                            'City' => $request->getShipperAddressCity(),
                            'StateOrProvinceCode' => $request->getShipperAddressStateOrProvinceCode(),
                            'PostalCode' => $request->getShipperAddressPostalCode(),
                            'CountryCode' => $request->getShipperAddressCountryCode(),
                        ],
                    ],
                    'Recipient' => [
                        'Contact' => [
                            'PersonName' => $request->getRecipientContactPersonName(),
                            'CompanyName' => $request->getRecipientContactCompanyName(),
                            'PhoneNumber' => $request->getRecipientContactPhoneNumber(),
                        ],
                        'Address' => [
                            'StreetLines' => [$request->getRecipientAddressStreet()],
                            'City' => $request->getRecipientAddressCity(),
                            'StateOrProvinceCode' => $request->getRecipientAddressStateOrProvinceCode(),
                            'PostalCode' => $request->getRecipientAddressPostalCode(),
                            'CountryCode' => $request->getRecipientAddressCountryCode(),
                            'Residential' => (bool)$this->getConfigData('residence_delivery'),
                        ],
                    ],
                    'ShippingChargesPayment' => [
                        'PaymentType' => $paymentType,
                        'Payor' => [
                            'AccountNumber' => $account,
                            'CountryCode' => $request->getShipperAddressCountryCode(),
                        ],
                    ],
                    'LabelSpecification' => [
                        'LabelFormatType' => 'COMMON2D',
                        'ImageType' => 'PNG',
                        'LabelStockType' => 'PAPER_8.5X11_TOP_HALF_LABEL',
                    ],
                    'RateRequestTypes' => ['ACCOUNT'],
                    'PackageCount' => 1,
                    'RequestedPackageLineItems' => [
                        'SequenceNumber' => '1',
                        'Weight' => ['Units' => $weightUnits, 'Value' => $request->getPackageWeight()],
                        'CustomerReferences' => [
                            'CustomerReferenceType' => 'CUSTOMER_REFERENCE',
                            'Value' => $referenceData,
                        ],
                        'SpecialServicesRequested' => [
                            'SpecialServiceTypes' => 'SIGNATURE_OPTION',
                            'SignatureOptionDetail' => ['OptionType' => $optionType],
                        ],
                    ],
                ],
            ];

            // for international shipping
            if ($request->getShipperAddressCountryCode() != $request->getRecipientAddressCountryCode()) {
                $requestClient['RequestedShipment']['CustomsClearanceDetail'] = [
                    'CustomsValue' => ['Currency' => $request->getBaseCurrencyCode(), 'Amount' => $customsValue],
                    'DutiesPayment' => [
                        'PaymentType' => $paymentType,
                        'Payor' => [
                            'AccountNumber' => $account,
                            'CountryCode' => $request->getShipperAddressCountryCode(),
                        ],
                    ],
                    'Commodities' => [
                        'Weight' => ['Units' => $weightUnits, 'Value' => $request->getPackageWeight()],
                        'NumberOfPieces' => 1,
                        'CountryOfManufacture' => implode(',', array_unique($countriesOfManufacture)),
                        'Description' => implode(', ', $itemsDesc),
                        'Quantity' => ceil($itemsQty),
                        'QuantityUnits' => 'pcs',
                        'UnitPrice' => ['Currency' => $request->getBaseCurrencyCode(), 'Amount' => $unitPrice],
                        'CustomsValue' => ['Currency' => $request->getBaseCurrencyCode(), 'Amount' => $customsValue],
                    ],
                ];
            }else{
                $requestClient['RequestedShipment']['CustomsClearanceDetail'] = [
                    'CustomsValue' => ['Currency' => $request->getBaseCurrencyCode(), 'Amount' => $customsValue],
                    'CommercialInvoice' => ['Purpose' => "SOLD"],
                    'DutiesPayment' => [
                        'PaymentType' => $paymentType,
                        'Payor' => [
                            'AccountNumber' => $account,
                            'CountryCode' => $request->getShipperAddressCountryCode(),
                        ],
                    ],
                    'Commodities' => [
                        'Weight' => ['Units' => $weightUnits, 'Value' => $request->getPackageWeight()],
                        'NumberOfPieces' => 1,
                        'CountryOfManufacture' => implode(',', array_unique($countriesOfManufacture)),
                        'Description' => implode(', ', $itemsDesc),
                        'Quantity' => ceil($itemsQty),
                        'QuantityUnits' => 'pcs',
                        'UnitPrice' => ['Currency' => $request->getBaseCurrencyCode(), 'Amount' => $unitPrice],
                        'CustomsValue' => ['Currency' => $request->getBaseCurrencyCode(), 'Amount' => $customsValue],
                    ],
                ];
            }

            if ($request->getMasterTrackingId()) {
                $requestClient['RequestedShipment']['MasterTrackingId'] = $request->getMasterTrackingId();
            }

            if ($request->getShippingMethod() == self::RATE_REQUEST_SMARTPOST) {
                $requestClient['RequestedShipment']['SmartPostDetail'] = [
                    'Indicia' => (double)$request->getPackageWeight() >= 1 ? 'PARCEL_SELECT' : 'PRESORTED_STANDARD',
                    'HubId' => $this->getConfigData('smartpost_hubid'),
                ];
            }

            // set dimensions
            if ($length || $width || $height) {
                $requestClient['RequestedShipment']['RequestedPackageLineItems']['Dimensions'] = [
                    'Length' => $length,
                    'Width' => $width,
                    'Height' => $height,
                    'Units' => $packageParams->getDimensionUnits() == Length::INCH ? 'IN' : 'CM'
                ];
            }

            return $this->_getVendorAuthDetails($request->getVendorId()) + $requestClient;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }


    /**
     * Return array of authenticated information
     *
     * @return array
     */
    protected function _getVendorAuthDetails($vendorId)
    {
        try {
            $account = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData('account',$vendorId)
                : $this->getConfigData('account');
            $meter_number = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData('meter_number',$vendorId)
                : $this->getConfigData('meter_number');
            $key = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData('key',$vendorId)
                : $this->getConfigData('key');
            $password = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData('password',$vendorId)
                : $this->getConfigData('password');

            return [
                'WebAuthenticationDetail' => [
                    'UserCredential' => [
                        'Key' => $key,
                        'Password' => $password,
                    ],
                ],
                'ClientDetail' => [
                    'AccountNumber' => $account,
                    'MeterNumber' => $meter_number,
                ],
                'TransactionDetail' => [
                    'CustomerTransactionId' => '*** Express Domestic Shipping Request v9 using PHP ***',
                ],
                'Version' => ['ServiceId' => 'ship', 'Major' => '10', 'Intermediate' => '0', 'Minor' => '0']
            ];
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }

    /**
     * For multi package shipments. Delete requested shipments if the current shipment
     * request is failed
     *
     * @param array $data
     * @return bool
     */
    public function rollBack($data): bool
    {
        $requestData = $this->_getVendorAuthDetails($data[0]["vendor_id"]);
        $requestData['DeletionControl'] = 'DELETE_ONE_PACKAGE';
        foreach ($data as &$item) {
            $requestData['TrackingId'] = $item['tracking_number'];
            $client = $this->_createShipSoapClient();
            $client->deleteShipment($requestData);
        }

        return true;
    }

    /**
     * Do request to shipment
     *
     * @param Request $request
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function requestToShipment($request)
    {
        try {
            $packages = $request->getPackages();
            if (!is_array($packages) || !$packages) {
                throw new LocalizedException(__('No packages for request'));
            }
            if ($request->getStoreId() != null) {
                $this->setStore($request->getStoreId());
            }
            $data = [];
            foreach ($packages as $packageId => $package) {
                $request->setPackageId($packageId);
                $request->setPackagingType($package['params']['container']);
                $request->setPackageWeight($package['params']['weight']);
                $request->setPackageParams(new \Magento\Framework\DataObject($package['params']));
                $request->setPackageItems($package['items']);
                $result = $this->_doShipmentRequest($request);

                if ($result->hasErrors()) {
                    $this->rollBack($data);
                    break;
                } else {
                    $data[] = [
                        'tracking_number' => $result->getTrackingNumber(),
                        'label_content' => $result->getShippingLabelContent(),
                        'vendor_id' => $request->getVendorId(),
                    ];
                }
                if (!isset($isFirstRequest)) {
                    $request->setMasterTrackingId($result->getTrackingNumber());
                    $isFirstRequest = false;
                }
            }

            $response = new \Magento\Framework\DataObject(['info' => $data]);
            if ($result->getErrors()) {
                $response->setErrors($result->getErrors());
            }

            return $response;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }

    /**
     * Get tracking
     *
     * @param string|string[] $trackings
     * @return Result|null
     */
    public function getTrackingVendor($trackings,$vendorId)
    {
        $this->setVendorTrackingReqeust($vendorId);

        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }

        foreach ($trackings as $tracking) {
            $this->_getVendorXMLTracking($tracking,$vendorId);
        }

        return $this->_result;
    }

    /**
     * Send request for tracking
     *
     * @param string[] $tracking
     * @return void
     */
    protected function _getVendorXMLTracking($tracking,$vendorId)
    {
        $account = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData('account',$vendorId)
            : $this->getConfigData('account');
        $meter_number = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData('meter_number',$vendorId)
            : $this->getConfigData('meter_number');
        $key = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData('key',$vendorId)
            : $this->getConfigData('key');
        $password = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData('password',$vendorId)
            : $this->getConfigData('password');

        $this->_result = $this->_trackFactory->create();
        $trackRequest = [
            'WebAuthenticationDetail' => [
                'UserCredential' => [
                    'Key' => $key,
                    'Password' => $password,
                ],
            ],
            'ClientDetail' => [
                'AccountNumber' => $account,
                'MeterNumber' => $meter_number,
            ],
            'Version' => [
                'ServiceId' => 'trck',
                'Major' => self::$trackServiceVersion,
                'Intermediate' => '0',
                'Minor' => '0',
            ],
            'SelectionDetails' => [
                'PackageIdentifier' => ['Type' => 'TRACKING_NUMBER_OR_DOORTAG', 'Value' => $tracking],
            ],
            'ProcessingOptions' => 'INCLUDE_DETAILED_SCANS'
        ];
        $requestString = serialize($trackRequest);
        $response = $this->_getCachedQuotes($requestString);
        $debugData = ['request' => $trackRequest];
        if ($response === null) {
            try {
                $client = $this->_createTrackSoapClient();
                $response = $client->track($trackRequest);
                $this->_setCachedQuotes($requestString, serialize($response));
                $debugData['result'] = $response;
            } catch (\Exception $e) {
                $debugData['result'] = ['error' => $e->getMessage(), 'code' => $e->getCode()];
                $this->_logger->critical($e);
            }
        } else {
            $response = @unserialize($response);
            $debugData['result'] = $response;
        }
        $this->_debug($debugData);


        $this->_parseTrackingResponse($tracking, $response);
    }

    /**
     * Parse tracking response
     *
     * @param string $trackingValue
     * @param \stdClass $response
     * @return void
     */
    protected function _parseTrackingResponse($trackingValue, $response) : void
    {
        if (!is_object($response) || empty($response->HighestSeverity)) {
            $this->appendTrackingError($trackingValue, __('Invalid response from carrier'));
            return;
        } elseif (in_array($response->HighestSeverity, self::$trackingErrors)) {
            $this->appendTrackingError($trackingValue, (string) $response->Notifications->Message);
            return;
        } elseif (empty($response->CompletedTrackDetails) || empty($response->CompletedTrackDetails->TrackDetails)) {
            $this->appendTrackingError($trackingValue, __('No available tracking items'));
            return;
        }

        $trackInfo = $response->CompletedTrackDetails->TrackDetails;

        // Fedex can return tracking details as single object instead array
        if (is_object($trackInfo)) {
            $trackInfo = [$trackInfo];
        }

        $result = $this->getResult();
        $carrierTitle = $this->getConfigData('title');
        $counter = 0;
        foreach ($trackInfo as $item) {
            $tracking = $this->_trackStatusFactory->create();
            $tracking->setCarrier(self::CODE);
            $tracking->setCarrierTitle($carrierTitle);
            $tracking->setTracking($trackingValue);
            $tracking->addData($this->processTrackingDetails($item));
            $result->append($tracking);
            $counter ++;
        }

        // no available tracking details
        if (!$counter) {
            $this->appendTrackingError(
                $trackingValue, __('For some reason we can\'t retrieve tracking info right now.')
            );
        }
    }


    /**
     * Parse track details response from Fedex
     * @param \stdClass $trackInfo
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function processTrackingDetails(\stdClass $trackInfo)
    {
        $result = [
            'shippeddate' => null,
            'deliverydate' => null,
            'deliverytime' => null,
            'deliverylocation' => null,
            'weight' => null,
            'progressdetail' => [],
        ];

        if (!empty($trackInfo->ShipTimestamp) &&
            ($datetime = \DateTime::createFromFormat(\DateTime::ISO8601, $trackInfo->ShipTimestamp)) !== false
        ) {
            $result['shippeddate'] = $datetime->format('Y-m-d');
        }

        $result['signedby'] = !empty($trackInfo->DeliverySignatureName) ?
            (string) $trackInfo->DeliverySignatureName :
            null;

        $result['status'] = (!empty($trackInfo->StatusDetail) && !empty($trackInfo->StatusDetail->Description)) ?
            (string) $trackInfo->StatusDetail->Description :
            null;
        $result['service'] = (!empty($trackInfo->Service) && !empty($trackInfo->Service->Description)) ?
            (string) $trackInfo->Service->Description :
            null;

        $datetime = $this->getDeliveryDateTime($trackInfo);
        if ($datetime) {
            $result['deliverydate'] = $datetime->format('Y-m-d');
            $result['deliverytime'] = $datetime->format('H:i:s');
        }

        $address = null;
        if (!empty($trackInfo->EstimatedDeliveryAddress)) {
            $address = $trackInfo->EstimatedDeliveryAddress;
        } elseif (!empty($trackInfo->ActualDeliveryAddress)) {
            $address = $trackInfo->ActualDeliveryAddress;
        }

        if (!empty($address)) {
            $result['deliverylocation'] = $this->getDeliveryAddress($address);
        }

        if (!empty($trackInfo->PackageWeight)) {
            $result['weight'] = sprintf(
                '%s %s',
                (string) $trackInfo->PackageWeight->Value,
                (string) $trackInfo->PackageWeight->Units
            );
        }

        if (!empty($trackInfo->Events)) {
            $events = $trackInfo->Events;
            if (is_object($events)) {
                $events = [$trackInfo->Events];
            }
            $result['progressdetail'] = $this->processTrackDetailsEvents($events);
        }


        return $result;
    }

    /**
     * Parse tracking details events from response
     * Return list of items in such format:
     * ['activity', 'deliverydate', 'deliverytime', 'deliverylocation']
     *
     * @param array $events
     * @return array
     */
    private function processTrackDetailsEvents(array $events)
    {
        $result = [];
        /** @var \stdClass $event */
        foreach ($events as $event) {
            $item = [
                'activity' => (string) $event->EventDescription,
                'deliverydate' => null,
                'deliverytime' => null,
                'deliverylocation' => null
            ];

            if (!empty($event->Timestamp)) {
                $datetime = \DateTime::createFromFormat(\DateTime::ISO8601, $event->Timestamp);
                $item['deliverydate'] = $datetime->format('Y-m-d');
                $item['deliverytime'] = $datetime->format('H:i:s');
            }

            if (!empty($event->Address)) {
                $item['deliverylocation'] = $this->getDeliveryAddress($event->Address);
            }

            $result[] = $item;
        }

        return $result;
    }



    /**
     * Set tracking request
     *
     * @return void
     */
    protected function setVendorTrackingReqeust($vendorId)
    {
        $r = new \Magento\Framework\DataObject();

        $account = $this->getAdminConfigData('carriers/usps/api_type')=='seller' ? $this->getVendorConfigData('account',$vendorId)
            : $this->getConfigData('account');

        $r->setAccount($account);

        $this->_rawTrackingRequest = $r;
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
