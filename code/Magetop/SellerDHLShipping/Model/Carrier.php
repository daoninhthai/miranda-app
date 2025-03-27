<?php
namespace Magetop\SellerDHLShipping\Model;

use Laminas\Http\Request as HttpRequest;
use Magento\Catalog\Model\Product\Type;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Magento\Framework\Xml\Security;
use Magento\Framework\Session\SessionManager;
use Magento\Shipping\Model\Shipping\LabelGenerator;

/**
 * Marketplace DHL shipping.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Carrier extends \Magento\Dhl\Model\Carrier
{
    /**
     * Code of the carrier.
     *
     * @var string
     */
    const CODE = 'mpdhl';
    /**
     * Code of the carrier.
     *
     * @var string
     */
    protected $_code = self::CODE;
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager = null;
    /**
     * [$_coreSession description].
     *
     * @var [type]
     */
    protected $_coreSession;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var [type]
     */
    protected $_region;
    /**
     * @var LabelGenerator
     */
    protected $_labelGenerator;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;
    /**
     * Rate result data.
     *
     * @var Result|null
     */
    protected $_result = null;

    protected $_customerFactory;

    protected $requestParam;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface             $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory     $rateErrorFactory
     * @param \Psr\Log\LoggerInterface                                       $logger
     * @param Security                                                       $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory               $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory                     $rateFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory    $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory                 $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory           $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory          $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory                         $regionFactory
     * @param \Magento\Directory\Model\CountryFactory                        $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory                       $currencyFactory
     * @param \Magento\Directory\Helper\Data                                 $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface           $stockRegistry
     * @param \Magento\Store\Model\StoreManagerInterface                     $storeManager
     * @param \Magento\Framework\Module\Dir\Reader                           $configReader
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\ObjectManagerInterface                      $objectManager
     * @param SessionManager                                                 $coreSession
     * @param \Magento\Customer\Model\Session                                $customerSession
     * @param LabelGenerator                                                 $labelGenerator
     * @param array                                                          $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Shipping\Helper\Carrier $carrierHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\Framework\Module\Dir\Reader $configReader,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Framework\Math\Division $mathDivision,
        \Magento\Framework\Filesystem\Directory\ReadFactory $readFactory,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\HTTP\LaminasClientFactory $httpClientFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        SessionManager $coreSession,
        \Magento\Customer\Model\Session $customerSession,
        LabelGenerator $labelGenerator,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\App\Request\Http $requestParam,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->requestParam = $requestParam;
        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $carrierHelper,
            $coreDate,
            $configReader,
            $storeManager,
            $string,
            $mathDivision,
            $readFactory,
            $dateTime,
            $httpClientFactory,
            $data
        );
        $this->_objectManager = $objectManager;
        $this->_coreSession = $coreSession;
        $this->_customerSession = $customerSession;
        $this->_region = $regionFactory;
        $this->_labelGenerator = $labelGenerator;
        $this->_customerFactory = $customerFactory;
    }

    /**
     * Collect and get rates.
     *
     * @param RateRequest $request
     *
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Error|bool|Result
     */
    public function collectRates(\Magento\Quote\Model\Quote\Address\RateRequest $request)
    {
        if (!$this->canCollectRates() || $this->_scopeConfig->getValue('carriers/mp_multishipping/active')) {
            return false;
        }
        $this->setRequest($request);
        $this->_result = $this->getShippingPricedetail($this->_rawRequest);

        return $this->getResult();
    }

    protected function _getGatewayUrl()
    {
        if ($this->getConfigData('sandbox_mode')) {
            return $this->getConfigData('sandbox_gateway_url');
        } else {
            return $this->getConfigData('production_gateway_url');
        }
    }

    /**
     * Prepare and set request to this instance.
     *
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     *
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function setRequest(\Magento\Framework\DataObject $request)
    {		
        $this->_request = $request;
        $r = new \Magento\Framework\DataObject();
        $mpassignproductId = 0;
        $shippingdetail = [];
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }
                //kien
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
                //end kien
                                                                                                                                
                $weight = $this->_getItemWeight($item);
                $originPostcode = '';
                $originCountryId = '';
                $originCity = '';
                if ($sellerId) {
                    $address = $this->_loadModel(
                        $sellerId,
                        'Magento\Customer\Model\Customer'
                    )->getDefaultShipping();

                    $addressModel = $this->_loadModel(
                        $address,
                        'Magento\Customer\Model\Address'
                    );
                    $originPostcode = $addressModel->getPostcode();
                    $originCountryId = $addressModel->getCountryId();
                    $originCity = $addressModel->getCity();
                } else {
                    $originPostcode = $this->_scopeConfig->getValue(
                        \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_ZIP,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $r->getStoreId()
                    );
                    $originCountryId = $this->_scopeConfig->getValue(
                        \Magento\Sales\Model\Order\Shipment::XML_PATH_STORE_COUNTRY_ID,
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                        $r->getStoreId()
                    );
                }
                if (count($shippingdetail) == 0) {
                    array_push(
                        $shippingdetail,
                        [
                            'seller_id' => $sellerId,
                            'origin_postcode' => $originPostcode,
                            'origin_country_id' => $originCountryId,
                            'origin_city' => $originCity,
                            'items_weight' => $weight,
                            'product_name' => $item->getName(),
                            'qty' => $item->getQty(),
                            'item_id' => $item->getId(),
                            'price' => $item->getPrice(),
            			    'product_id' => $item->getProductId(),
            			    'product_id-Qty' => $item->getQty(),
                        ]
                    );
                } else {
                    $shipinfoflag = true;
                    $index = 0;
                    foreach ($shippingdetail as $itemship) {
                        if ($itemship['seller_id'] == $sellerId) {
                            $itemship['items_weight'] = $itemship['items_weight'] + $weight;
                            $itemship['product_name'] = $itemship['product_name'].','.$item->getName();
                            $itemship['item_id'] = $itemship['item_id'].','.$item->getId();
                            $itemship['qty'] = $itemship['qty'] + $item->getQty();
                            $itemship['price'] = $itemship['price'] + $item->getPrice();
            			    $itemship['product_id'] = $itemship['product_id'].','.$item->getProductId();
            			    $itemship['product_id-Qty'] = $itemship['product_id-Qty'].','.$item->getQty();
                            $shippingdetail[$index] = $itemship;
                            $shipinfoflag = false;
                        }
                        ++$index;
                    }
                    if ($shipinfoflag == true) {
                        array_push(
                            $shippingdetail,
                            [
                                'seller_id' => $sellerId,
                                'origin_postcode' => $originPostcode,
                                'origin_country_id' => $originCountryId,
                                'origin_city' => $originCity,
                                'items_weight' => $weight,
                                'product_name' => $item->getName(),
                                'qty' => $item->getQty(),
                                'item_id' => $item->getId(),
                                'price' => $item->getPrice(),
                				'product_id' => $item->getProductId(),
                				'product_id-Qty' => $item->getQty(),
                            ]
                        );
                    }
                }
            }
        }
        if ($request->getShippingDetails()) {
            $shippingdetail = $request->getShippingDetails();
        }
        $r->setShippingDetails($shippingdetail);

        if ($request->getDestCountryId()) {
            $destCountry = $request->getDestCountryId();
        } else {
            $destCountry = self::USA_COUNTRY_ID;
        }
        if ($destCountry == self::USA_COUNTRY_ID
            && ($request->getDestPostcode() == '00912' || $request->getDestRegionCode() == self::PUERTORICO_COUNTRY_ID)
        ) {
            $destCountry = self::PUERTORICO_COUNTRY_ID;
        }
        $r->setDestCountryId($destCountry);

        if ($request->getDestPostcode()) {
            $r->setDestPostal($request->getDestPostcode());
        }

        $r->setDestCity($request->getDestCity());
        $r->setOrigCity($request->getOrigCity());

        $this->setRawRequest($r);
        return $this;
    }
    
    /**
     * set the configuration values.
     *
     * @param \Magento\Framework\DataObject $request
     */
    public function setConfigData(\Magento\Framework\DataObject $request)
    {
        $r = $request;

        $r->setDhlAccessId($this->getConfigData('id'));
        $r->setDhlPassword($this->getConfigData('password'));
        $r->setDhlAccountNumber($this->getConfigData('account'));
        $r->setDhlReadyTime($this->getConfigData('ready_time'));

        return $r;
    }
    
    /**
     * set seller credentials if he/she has.
     *
     * @param \Magento\Framework\DataObject $request
     * @param int                           $sellerId
     *
     * @return \Magento\Framework\DataObject
     */
    protected function _isSellerHasOwnCredentials(\Magento\Framework\DataObject $request, $sellerId)
    {
        $customer = $this->_customerFactory->create()->load($sellerId);
        if (isset($customer['dhl_access_id'])) {
            $request->setDhlAccessId($customer->getDhlAccessId());
        } elseif ($this->getConfigData('id') == '' && !isset($customer['dhl_access_id'])) {
            $request->setDhlAccessId('');
        }
        if (isset($customer['dhl_account_number'])) {
            $request->setDhlAccountNumber($customer->getDhlAccountNumber());
        } elseif ($this->getConfigData('account') == '' && !isset($customer['dhl_account_number'])) {
            $request->setDhlAccountNumber('');
        }
        if (isset($customer['dhl_password'])) {
            $request->setDhlPassword($customer->getDhlPassword());
        } elseif ($this->getConfigData('password') == '' && !isset($customer['dhl_password'])) {
            $request->setDhlPassword('');
        }
        if (isset($customer['dhl_ready_time'])) {
            $request->setDhlReadyTime($customer->getDhlReadyTime());
        } elseif ($this->getConfigData('ready_time') == '' && !isset($customer['dhl_ready_time'])) {
            $request->setDhlReadyTime('');
        }

        return $request;
    }
    
    /**
     * Get shipping quotes from DHL service
     *
     * @param string $request
     * @return string
     */
    protected function _getQuotesFromServer($request)
    {
        $client = $this->_httpClientFactory->create();
        $client->setUri((string)$this->_getGatewayUrl());
        $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
        $client->setRawData(utf8_encode($request));

        return $client->request(Request::METHOD_POST)->getBody();
    }
    
    /**
     * Makes remote request to the carrier and returns a response.
     *
     * @param string $purpose
     *
     * @return mixed
     */
    protected function _createRatesRequest($shipdetail)
    {
        $rawRequest = $this->_rawRequest;
        $rawRequest->setOrigCountryId($shipdetail['origin_country_id']);
        $xmlStr = '<?xml version = "1.0" encoding = "UTF-8"?>'.
            '<p:DCTRequest xmlns:p="https://www.dhl.com" xmlns:p1="https://www.dhl.com/datatypes" '.
            'xmlns:p2="https://www.dhl.com/DCTRequestdatatypes" '.
            'xmlns:xsi="https://www.w3.org/2001/XMLSchema-instance" '.
            'xsi:schemaLocation="https://www.dhl.com DCT-req.xsd "/>';
        $xml = $this->_xmlElFactory->create(['data' => $xmlStr]);
        $nodeGetQuote = $xml->addChild('GetQuote', '', '');
        $nodeRequest = $nodeGetQuote->addChild('Request');

        $nodeServiceHeader = $nodeRequest->addChild('ServiceHeader');
        $nodeServiceHeader->addChild('SiteID', (string) $rawRequest->getDhlAccessId());
        $nodeServiceHeader->addChild('Password', (string) $rawRequest->getDhlPassword());

        $nodeFrom = $nodeGetQuote->addChild('From');
        $nodeFrom->addChild('CountryCode', $shipdetail['origin_country_id']);
        $nodeFrom->addChild('Postalcode', $shipdetail['origin_postcode']);
        $nodeFrom->addChild('City', $shipdetail['origin_city']);

        $nodeBkgDetails = $nodeGetQuote->addChild('BkgDetails');
        $nodeBkgDetails->addChild('PaymentCountryCode', $shipdetail['origin_country_id']);
        $nodeBkgDetails->addChild(
            'Date',
            (new \DateTime())->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
        );
        $nodeBkgDetails->addChild('ReadyTime', 'PT'.(int) (string) $rawRequest->getDhlReadyTime().'H00M');
        $nodeBkgDetails->addChild('DimensionUnit', $this->_getDimensionUnit());
        $nodeBkgDetails->addChild('WeightUnit', $this->_getWeightUnit());

        $nodePieces = $nodeBkgDetails->addChild('Pieces', '', '');
        $nodePiece = $nodePieces->addChild('Piece', '', '');
        $nodePiece->addChild('PieceID', 1);
        //$nodePiece->addChild('Weight', $shipdetail['items_weight']);
	    //QUITAMOS LAS MEDIDAS ESTATICAS DEL PLUGIN
        //METEMOS LAS MEDIDAS DINAMICAS DE CADA PRODUCTO
        //$this->_addDimension($nodePiece);

        $products_ids = explode(',',$shipdetail['product_id']??'');
        $cantidad_productos = count($products_ids);
        $products_Cant = explode(',',$shipdetail['product_id-Qty']??'');

        if($cantidad_productos==1){
            $height = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($shipdetail['product_id'])->getAlto();
            $depth = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($shipdetail['product_id'])->getProfundidad();
            $width = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($shipdetail['product_id'])->getAncho();
            if ($height && $depth && $width) {
                $nodePiece->addChild('Height', $height);
                $nodePiece->addChild('Depth', $depth);
                $nodePiece->addChild('Width', $width * $shipdetail['qty']);
            }
        }else{//MAS DE UN PRODUCTO
            $heightArr = array();
            $depthArr = array();
            $widthArr = array();
            $i=0;
            foreach ($products_ids as &$product_id) {
                $heightArr[] = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($product_id)->getAlto();
                $depthArr[] = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($product_id)->getProfundidad();
                $widthArr[] = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($product_id)->getAncho() * $products_Cant[$i];
                $i++;
            }	
            $maxHeight=max($heightArr);
            $maxDepth=max($depthArr);
            $sumaWidth=array_sum($widthArr);   	
            
            if ($maxHeight && $maxDepth && $sumaWidth) {
                $nodePiece->addChild('Height', $maxHeight);
                $nodePiece->addChild('Depth', $maxDepth);
                $nodePiece->addChild('Width', $sumaWidth);
            }
        }

        $nodePiece->addChild('Weight', $shipdetail['items_weight']);
        $nodeBkgDetails->addChild('PaymentAccountNumber', (string) $rawRequest->getDhlAccountNumber());
        $nodeTo = $nodeGetQuote->addChild('To');
        $nodeTo->addChild('CountryCode', $rawRequest->getDestCountryId());
        $nodeTo->addChild('Postalcode', $rawRequest->getDestPostal());
        $nodeTo->addChild('City', $rawRequest->getDestCity());
      
        if (
            self::DHL_CONTENT_TYPE_NON_DOC == $this->getConfigData('content_type') &&
            $rawRequest->getOrigCountryId() != $rawRequest->getDestCountryId()
        ) {
            // IsDutiable flag and Dutiable node indicates that cargo is not a documentation
            $nodeBkgDetails->addChild('IsDutiable', 'Y');
            $nodeDutiable = $nodeGetQuote->addChild('Dutiable');
            $baseCurrencyCode = $this->_storeManager
                ->getWebsite($this->_request->getWebsiteId())
                ->getBaseCurrencyCode();
            $nodeDutiable->addChild('DeclaredCurrency', $baseCurrencyCode);
            $nodeDutiable->addChild('DeclaredValue', sprintf('%.2F', $shipdetail['price']));
        }

        return $xml;
    }

    /**
     * Build RateV3 request, send it to Dhl gateway and retrieve quotes in XML format.
     *
     * @return Result
     */
    public function getShippingPricedetail(\Magento\Framework\DataObject $request)
    {
        $this->setConfigData($request);
        $this->setRawRequest($request);
        $r = $request;
        $submethod = [];
        $shippinginfo = [];
        $totalpric = [];
        $totalPriceArr = [];
        $serviceCodeToActualNameMap = [];
        $costArr = [];
        $debugData = [];
        $price = 0;
        $flag = false;
        $check = false;

        foreach ($r->getShippingDetails() as $shipdetail) {
            $priceArr = [];
            $this->_isSellerHasOwnCredentials($this->_rawRequest, $shipdetail['seller_id']);
            $responseBody = '';
            try {
                for ($offset = 0; $offset <= self::UNAVAILABLE_DATE_LOOK_FORWARD; ++$offset) {
                    $debugData['try-'.$offset] = [];
                    $debugPoint = &$debugData['try-'.$offset];

                    $requestXml = $this->_createRatesRequest($shipdetail);

                    $date = date(self::REQUEST_DATE_FORMAT, strtotime($this->_getShipDate()." +{$offset} days"));
                    $this->_setQuotesRequestXmlDate($requestXml, $date);

                    $requestData = $requestXml->asXML();
                    $debugPoint['request'] = $requestData;
                    $responseBody = $this->_getCachedQuotes($requestData);
                    $debugPoint['from_cache'] = $responseBody === null;

                    if ($debugPoint['from_cache']) {
                        $responseBody = $this->_getQuotesFromServer($requestData);
                    }

                    $debugPoint['response'] = $responseBody;

                    $bodyXml = $this->_xmlElFactory->create(['data' => $responseBody]);
                    $code = $bodyXml->xpath('//GetQuoteResponse/Note/Condition/ConditionCode');
                    if (isset($code[0]) && (int) $code[0] == self::CONDITION_CODE_SERVICE_DATE_UNAVAILABLE) {
                        $debugPoint['info'] = sprintf(__('DHL service is not available at %s date'), $date);
                    } else {
                        break;
                    }

                    $this->_setCachedQuotes($requestData, $responseBody);
                }
                $this->_debug($debugData);
            } catch (\Exception $e) {
                $this->_errors[$e->getCode()] = $e->getMessage();
            }

            list($priceArr, $costArr) = $this->_parseRateResponse($responseBody);
            $price = 0;
            if (count($totalPriceArr) > 0) {
                foreach ($priceArr as $method => $price) {
                    if (array_key_exists($method, $totalPriceArr)) {
                        $check = true;
                        $totalPriceArr[$method] = $totalPriceArr[$method] + $priceArr[$method];
                    } else {
                        unset($priceArr[$method]);
                        $flag = $check == true ? false : true;
                    }
                }
            } else {
                $totalPriceArr = $priceArr;
            }
            if (count($priceArr) > 0) {
                foreach ($totalPriceArr as $method => $price) {
                    if (!array_key_exists($method, $priceArr)) {
                        unset($totalPriceArr[$method]);
                    }
                }
            } else {
                $totalPriceArr = [];
                $flag = true;
            }
            if ($flag) {
                $debugData['result'] = ['error' => 1];
                if ($this->_scopeConfig->getValue('carriers/mp_multishipping/active')) {
                    return [];
                } else {
                    return $this->_parseXmlResponse($debugData);
                }
            }
            $submethod = [];

            foreach ($priceArr as $index => $price) {
                $submethod[$index] = [
                    'method' => $this->getDhlProductTitle($index).' (DHL)',
                    'cost' => $price,
                    'error' => 0,
                ];
            }
            array_push(
                $shippinginfo,
                [
                    'seller_id' => $shipdetail['seller_id'],
                    'methodcode' => $this->_code,
                    'shipping_ammount' => $price,
                    'product_name' => $shipdetail['product_name'],
                    'submethod' => $submethod,
                    'item_ids' => $shipdetail['item_id'],
                ]
            );
        }
        $totalpric = ['totalprice' => $totalPriceArr, 'costarr' => $costArr];
        $debugData['result'] = $totalpric;
        $result = ['handlingfee' => $totalpric, 'shippinginfo' => $shippinginfo, 'error' => $debugData];
        $shippingAll = $this->_coreSession->getData('shippinginfo');
        $shippingAll = $this->_coreSession->getShippingInfo();
        $shippingAll[$this->_code] = $result['shippinginfo'];
        $this->_coreSession->setShippingInfo($shippingAll);

        if ($this->_scopeConfig->getValue('carriers/mp_multishipping/active')) {
            return $result;
        } else {
            return $this->_parseXmlResponse($totalpric);
        }
    }

    /**
     * Get the rates from response.
     * @param  xml $response
     * @return array [$priceArr, $costArr];
     */
    public function _parseRateResponse($response)
    {
        $priceArr = [];
        $costArr = [];
        if (strlen(trim($response)) > 0) {
            if (@strpos(trim($response), '<?xml') === 0) {
                $xml = $this->parseXml($response, 'Magento\Shipping\Model\Simplexml\Element');
                if (is_object($xml)) {
                    if (isset($xml->GetQuoteResponse->BkgDetails->QtdShp)) {
                        foreach ($xml->GetQuoteResponse->BkgDetails->QtdShp as $quotedShipment) {
                            $dhlProduct = (string) $quotedShipment->GlobalProductCode;
                            $totalEstimate = (float) (string) $quotedShipment->ShippingCharge;
                            $currencyCode = (string) $quotedShipment->CurrencyCode;
                            $baseCurrencyCode = $this->_storeManager->getStore()->getBaseCurrencyCode();
                            $dhlProductDescription = $this->getDhlProductTitle($dhlProduct);
                            $serviceCodeToActualNameMap[$dhlProduct] = $dhlProductDescription;
                            if ($currencyCode != $baseCurrencyCode) {
                                /* @var $currency \Magento\Directory\Model\Currency */
                                $currency = $this->_currencyFactory->create();
                                $rates = $currency->getCurrencyRates($currencyCode, [$baseCurrencyCode]);
                                if (!empty($rates) && isset($rates[$baseCurrencyCode])) {
                                    // Convert to store display currency using store exchange rate
                                    $totalEstimate = $totalEstimate * $rates[$baseCurrencyCode];
                                } else {
                                    $rates = $currency->getCurrencyRates($baseCurrencyCode, [$currencyCode]);
                                    if (!empty($rates) && isset($rates[$currencyCode])) {
                                        $totalEstimate = $totalEstimate / $rates[$currencyCode];
                                    }
                                    if (!isset($rates[$currencyCode]) || !$totalEstimate) {
                                        $totalEstimate = false;
                                        $this->_errors[] = __(
                                            'We had to skip DHL method %1 because we couldn\'t 
                                            find exchange rate %2 (Base Currency).',
                                            $currencyCode,
                                            $baseCurrencyCode
                                        );
                                    }
                                }
                            }
                            if (
                                array_key_exists(
                                    (string) $quotedShipment->GlobalProductCode, 
                                    $this->getAllowedMethods()
                                )
                            ) {
                                if ($totalEstimate) {
                                    $costArr[$dhlProduct] = $totalEstimate;
                                    $priceArr[$dhlProduct] = $this->getMethodPrice($totalEstimate, $dhlProduct);
                                }
                            }
                        }
                        asort($priceArr);

                        return [$priceArr, $costArr];
                    } else {
                        return [$priceArr, $costArr];
                    }
                }
            }
        }
    }
    
    /**
     * Parse calculated rates.
     *
     * @param string $response
     *
     * @return Result
     */
    protected function _parseXmlResponse($response)
    {
        $result = $this->_rateFactory->create();
        if (isset($response['result']['error'])) {
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier('mpdhl');
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($this->getConfigData('specificerrmsg'));
            $result->append($error);
        } else {
            $totalPriceArr = $response['totalprice'];
            $costArr = $response['costarr'];
            foreach ($totalPriceArr as $method => $price) {
                $rate = $this->_rateMethodFactory->create();
                $rate->setCarrier('mpdhl');
                $rate->setCarrierTitle($this->getConfigData('title'));
                $rate->setMethod($method);
                $rate->setMethodTitle($this->getDhlProductTitle($method));
                $rate->setCost($costArr[$method]);
                $rate->setPrice($price);
                $result->append($rate);
            }
        }

        return $result;
    }
    
    /**
     * Prepare shipping label data.
     *
     * @param \SimpleXMLElement $xml
     *
     * @return \Magento\Framework\DataObject
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareShippingLabelContent(\SimpleXMLElement $xml)
    {
        $result = new \Magento\Framework\DataObject();
        try {
            if (!isset($xml->AirwayBillNumber) || !isset($xml->LabelImage->OutputImage)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Unable to retrieve shipping label'));
            }
            $result->setTrackingNumber((string) $xml->AirwayBillNumber);
            $labelContent[] = base64_decode((string) $xml->LabelImage->OutputImage);

            $outputPdf = $this->_labelGenerator->combineLabelsPdf($labelContent);
            $result->setShippingLabelContent($outputPdf->render());
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        }

        return $result;
    }
    
    /**
     * Do request to shipment
     *
     * @param \Magento\Shipping\Model\Shipment\Request $request
     * @return array|\Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function requestToShipment($request)
    {
        $packages = $request->getPackages();
        $this->setRawRequest($request);
        $orderId = $request->getOrderId();
        $request->setOrderId($orderId);

        if (!is_array($packages) || !$packages) {
            throw new \Magento\Framework\Exception\LocalizedException(__('No packages for request'));
        }
        $result = $this->_doShipmentRequest($request);

        $shippment_data = $this->_customerSession->getData('shipment_data');
        $response = new \Magento\Framework\DataObject(
            [
                'info' => [
                    [
                        'tracking_number' => $shippment_data['tracking_number'],
                        'label_content' => $shippment_data['api_name'],
                    ],
                ],
            ]
        );

        $request->setMasterTrackingId($shippment_data['tracking_number']);

        return $response;
    }

    /**
     * Do shipment request to carrier web service,.
     *
     * @param \Magento\Framework\DataObject $request
     *
     * @return \Magento\Framework\DataObject
     */
    public function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $orderId = $request->getOrderId();
        $customerId = $this->_customerSession->getCustomerId();
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
        $this->_order = $order;
        if ($this->_isShippingMethod()) {
            $this->setShipemntRequest();
            $response = $this->_createShipmentRequest();
            if (strlen(trim($response)) > 0) {
                if (@strpos(trim($response), '<?xml') === 0) {
                    $xml = $this->parseXml($response, 'Magento\Shipping\Model\Simplexml\Element');
                    if (is_object($xml)) {
                        if (in_array($xml->getName(), ['ErrorResponse', 'ShipmentValidateErrorResponse'])
                        || isset($xml->GetQuoteResponse->Note->Condition)
                        ) {
                            $code = null;
                            $data = null;
                            if (isset($xml->Response->Status->Condition)) {
                                $nodeCondition = $xml->Response->Status->Condition;
                            }
                            foreach ($nodeCondition as $condition) {
                                $code = isset($condition->ConditionCode) ? (string) $condition->ConditionCode : 0;
                                $data = isset($condition->ConditionData) ? (string) $condition->ConditionData : '';
                                if (!empty($code) && !empty($data)) {
                                    break;
                                }
                            }
                            throw new \Magento\Framework\Exception\LocalizedException(
                                __('Error #%1 : %2', trim($code), trim($data))
                            );
                        } elseif (isset($xml->AirwayBillNumber)) {
                            $labelResponse = $this->_prepareShippingLabelContent($xml);

                            $sellerOrders = $this->_objectManager->create(
                                'Magetop\Marketplace\Model\Saleslist'
                            )->getCollection()
                            ->addFieldToFilter('sellerid', ['eq' => $customerId])
                            ->addFieldToFilter('orderid', ['eq' => $orderId]);

                            $shipmentData = [
                                'api_name' => 'DHL',
                                'tracking_number' => $labelResponse->getTrackingNumber()
                            ];
                            $this->_customerSession->setData('shipment_data', $shipmentData);

                            foreach ($sellerOrders as $row) {
                                $row->setShipmentLabel($labelResponse->getShippingLabelContent());
                                $row->save();
                            }
                        }
                    }
                }
            }
            return true;
        } else {
            return false;
        }
    }
    
    /**
     * Make DHL Shipment Request
     * @return string xml
     */
    protected function _createShipmentRequest()
    {
        $order = $this->_order;
        $customerId = $this->_customerSession->getCustomerId();

        $request = $this->_rawRequest;

        $request->setOrigCountryId($request->getShipperAddressCountryCode());

        $originRegionCode = $this->getCountryParams(
            $request->getShipperAddressCountryCode()
        )->getRegion();

        if (!$originRegionCode) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Wrong Region'));
        }

        if ($originRegionCode == 'AM') {
            $originRegionCode = '';
        }

        $xmlStr = '<?xml version="1.0" encoding="UTF-8"?>'.
            '<req:ShipmentValidateRequest'.
            $originRegionCode.
            ' xmlns:req="https://www.dhl.com"'.
            ' xmlns:xsi="https://www.w3.org/2001/XMLSchema-instance"'.
            ' xsi:schemaLocation="https://www.dhl.com ship-val-req'.
            ($originRegionCode ? '_'.
                $originRegionCode : '').
            '.xsd" />';
        $xml = $this->_xmlElFactory->create(['data' => $xmlStr]);

        $nodeRequest = $xml->addChild('Request', '', '');
        $nodeServiceHeader = $nodeRequest->addChild('ServiceHeader');
        $nodeServiceHeader->addChild('SiteID', (string) $request->getAccessId());
        $nodeServiceHeader->addChild('Password', (string) $request->getPassword());

        if (!$originRegionCode) {
            $xml->addChild('RequestedPickupTime', 'N', '');
        }
        $xml->addChild('NewShipper', 'N', '');
        $xml->addChild('LanguageCode', 'EN', '');
        $xml->addChild('PiecesEnabled', 'Y', '');

        /* Billing */
        $nodeBilling = $xml->addChild('Billing', '', '');
        $nodeBilling->addChild('ShipperAccountNumber', (string) $request->getAccountNumber());
        /*
         * Method of Payment:
         * S (Shipper)
         * R (Receiver)
         * T (Third Party)
         */
        $nodeBilling->addChild('ShippingPaymentType', 'S');

        /*
         * Shipment bill to account ÃƒÂ¢Ã¢â€šÂ¬Ã¢â‚¬Å“ required if Shipping PaymentType is other than 'S'
         */
        $nodeBilling->addChild('BillingAccountNumber', (string) $request->getAccountNumber());
        $nodeBilling->addChild('DutyPaymentType', 'S');
        $nodeBilling->addChild('DutyAccountNumber', (string) $request->getAccountNumber());

        /* Receiver */
        $nodeConsignee = $xml->addChild('Consignee', '', '');

        $companyName = $request->getRecipientContactCompanyName() ? $request
            ->getRecipientContactCompanyName() : $request
            ->getRecipientContactPersonFullName();

        $nodeConsignee->addChild('CompanyName', substr($companyName, 0, 35));

        $address = $request->getRecipientAddressStreet1().' '.$request->getRecipientAddressStreet2();
        $address = $this->string->split($address, 35, false, true);
        if (is_array($address)) {
            foreach ($address as $addressLine) {
                $nodeConsignee->addChild('AddressLine', $addressLine);
            }
        } else {
            $nodeConsignee->addChild('AddressLine', $address);
        }

        $nodeConsignee->addChild('City', $request->getRecipientAddressCity());
        $nodeConsignee->addChild('Division', $request->getRecipientAddressStateOrProvinceCode());
        $nodeConsignee->addChild('PostalCode', $request->getRecipientAddressPostalCode());
        $nodeConsignee->addChild('CountryCode', $request->getRecipientAddressCountryCode());
        $nodeConsignee->addChild(
            'CountryName',
            $this->getCountryParams($request->getRecipientAddressCountryCode())->getName()
        );
        $nodeContact = $nodeConsignee->addChild('Contact');
        $nodeContact->addChild('PersonName', substr($request->getRecipientContactPersonFullName(), 0, 34));
        $nodeContact->addChild('PhoneNumber', substr($request->getRecipientContactPhoneNumber(), 0, 24));

        /*
         * Commodity
         * The CommodityCode element contains commodity code for shipment contents. Its
         * value should lie in between 1 to 9999.This field is mandatory.
         */
        $nodeCommodity = $xml->addChild('Commodity', '', '');
        $nodeCommodity->addChild('CommodityCode', '1');

        /* Dutiable */
        if ($this->isDutiable(
            $request->getShipperAddressCountryCode(),
            $request->getRecipientAddressCountryCode()
        )) {
            $nodeDutiable = $xml->addChild('Dutiable', '', '');
            $nodeDutiable->addChild(
                'DeclaredValue',
                sprintf('%.2F', $request->getTotalValue())
            );
            $baseCurrencyCode = $this->_storeManager->getStore()->getBaseCurrencyCode();
            $nodeDutiable->addChild('DeclaredCurrency', $baseCurrencyCode);
        }

        /*
         * Reference
         * This element identifies the reference information. It is an optional field in the
         * shipment validation request. Only the first reference will be taken currently.
         */
        $nodeReference = $xml->addChild('Reference', '', '');
        $nodeReference->addChild('ReferenceID', $order->getIncrementId());
        $nodeReference->addChild('ReferenceType', 'St');

        /* Shipment Details */
        $this->_setItemsDetails($xml, $request, $originRegionCode);

        /* Shipper */              
        $nodeShipper = $xml->addChild('Shipper', '', '');
        $nodeShipper->addChild('ShipperID', (string) $request->getAccountNumber());
        $nodeShipper->addChild('CompanyName', $request->getShipperContactCompanyName());
        $nodeShipper->addChild('RegisteredAccount', (string) $request->getAccountNumber());

        $address = $request->getShipperAddressStreet1().' '.$request->getShipperAddressStreet2();
        $address = $this->string->split($address, 35, false, true);
        if (is_array($address)) {
            foreach ($address as $addressLine) {
                $nodeShipper->addChild('AddressLine', $addressLine);
            }
        } else {
            $nodeShipper->addChild('AddressLine', $address);
        }

        $nodeShipper->addChild('City', $request->getShipperAddressCity());
        $nodeShipper->addChild('Division', $request->getShipperAddressStateOrProvinceCode());
        $nodeShipper->addChild('PostalCode', $request->getShipperAddressPostalCode());
        $nodeShipper->addChild('CountryCode', $request->getShipperAddressCountryCode());
        $nodeShipper->addChild(
            'CountryName',
            $this->getCountryParams($request->getShipperAddressCountryCode())->getName()
        );
        $nodeContact = $nodeShipper->addChild('Contact', '', '');
        $nodeContact->addChild('PersonName', substr($request->getShipperContactPersonFullName(), 0, 34));
        $nodeContact->addChild('PhoneNumber', substr($request->getShipperContactPhoneNumber(), 0, 24));     

        $xml->addChild('LabelImageFormat', 'PDF', '');

        // If seller and Admin want to display custom logo.
        list($logoPath, $extension) = $this->_getLogoImage();
       
        if ($logoPath != '') {
            //$logo = base64_encode(file_get_contents($logoPath));
            $logo = 'iVBORw0KGgoAAAANSUhEUgAAAP8AAAEGCAYAAACq4kOvAAAAAXNSR0IArs4c6QAAAARnQU1BAACxjwv8YQUAAAAJcEhZcwAADsQAAA7EAZUrDhsAACQ0SURBVHhe7ZyHm1XV3e/vH3Kf5733uXlTbLShCtKEoSkgTUQRsABRVCwERJSIXRONsfFGBQEFS6KS2AtqosYkCCZGQIogvQzDtDOnzvf+vmvtdc6eARMTk0wm6/vRdfbeq5951mettfc5h/8FIUSUSH4hIkXyCxEpkl+ISJH8QkSK5BciUiS/EJEi+YWIFMkvRKRIfiEiRfILESmSX4hIkfxCRIrkFyJSJL8QkSL5hYgUyS9EpEh+ISJF8gsRKZJfiEiR/EJEiuQXIlIkvxCRIvmFiBTJL0SkSH4hIkXyCxEpkl+ISJH8QkSK5BciUiS/EJEi+YWIFMkvRKRIfiEiRfILESmSX4hIkfxCRIrkFyJSJL8QkSL5hYgUyS9EpEh+ISJF8gsRKZJfiEiR/EJEiuQXIlIkvxCRIvmFiBTJL0SkSH4hIkXyCxEpkl+ISJH8QkSK5BciUiS/EJEi+YWIFMkvRKRIfiEiRfILESmSX4hIkfxCRIrkFyJSJL8QkSL5hYgUyS9EpEh+ISJF8gsRKZJfiEiR/EJEiuQXIlIkvxCRIvmFiBTJL0SkSH4hIkXyCxEpkl+ISJH8QkSK5BciUiS/EJEi+YWIFMkvRKRIfiEiRfILESmSX4hIkfxCRIrkFyJSJL8QkSL5hYgUyS9EpEh+ISJF8gsRKZJfiEiR/EJEiuQXIlIkvxCRIvmFiBTJL0SkSH4hIkXyCxEpkl+ISJH8QkSK5BciUiS/EJEi+YWIFMkvRKRIfiEiRfILESmSX4hIkfxCRIrkFyJSJL8QkSL5xb83LcmxXSklx/8sJL9oV+h2CB6Klgot4WiHhOPL/P0cV0+6cpfA9gvJkcFTTu7ASH7RrlCgYnL0MgXZ24ZyhvJpcvmNOK6eVpVbuy5Q/hA8x5XrgEh+0a6kXWslU9uEEP7BfGW1LlLyC/FPI0j0lTL91QzfjK+s1kVKfiH+iXjBgkxtw/EEIUP4ZrQkbR+HiwxtfLX8XxWOJ93ndGg/JL9oZ7wEJxIoHSok0rSYiAzfUCAv/wnqKDfMNMkvxD+B1hIEefgQsPWDwIDldQ8A/zHyt23/eEJ6a/lDx0L/2objCfWk6+Ox/ZD8op0JMniCPP8+8hOmn7it0L+24ThcZGgr1MfQfkh+0c4EGTxtJWIoVpINn78ln7NEO2cGo6XFNu8ln5HnDF8PX587S8qHOiuEPEl6Cmb1Nw7WZnKdaba+taVkeQqczqyOliIK+YydF8pt5vN5dzxRv4tFlvNks9nkzGoK/f07kfyinWktVRCobSBODPs/m2lunWBQkFzOS/e3iF8qmYAtvhzrCF8roFeVakIfGVpDAUNXilYwl7c6XIovXywmVzzPF6zusINovZMIIhcKBd+PpHGec2Kg9F//fX09JL/4NyRI4Y8UI4gdCIte23gKElbKv74yMr2EYsEmkwD9spAcyiHk9cHwkUbJ5ORqbJNAImeh0LrdVpOA5XMTTnkSqKz6hPL/NcL7+6ZIfvFvBoWgAOngJTl69Kg7UiPbRbtAMhluoVtvib/eKlmy7TfLeOFYJpc1ia250AaPvib2IRXKCVZHkZNHmAQ8nHfytgtgIOwO4/yq7rf//mitp4RPi83Jq+11enL7pkh+0a5U5CIUiyJwJUyCieVXZqZZjCXnbOwztWAFXfmU6EGktuKcGNbp22Q5V4+rsHwoh0reJLhITh6cePI2YXAHksRbyGRsIgiFLaROkStY/nDdpu/hOh2fjvtHiU8kv2hXnAD+1KA8Jm8LxUkFk6uh/pjLwaHfbC9ULW8FWbapqYlJTvi/DVutc82JuFYft99Jh7K5yr08g+tbOrhIHsNEVbC62A+rM2v1WTp3EZkmX2dz1u7bcya35QihOVe5j0/Lnt4JNDf7W5J0Oknn+XuR/KJdCSJ4KBZlofS2oiZh/jVz0Kd3d3TtWoWTTu2K86bPxm83bnUTQM5mgBdffBHV1dWoqanxtdgkkF5Fvxov/9lnDcdjjz3mYormlC3Mrk95u3ev9C8lPoOL5JG9yKKUN/Ft4tr95U7MnjkLXTp3R+dOVRh3ziR8+MHvy/U05fLI2f0EJ7G8NXYi+cP5tm3b8NRTT7nVnpPAifJ8EyS/aFc4hEPwcEVLVvySyV88hpsXXIWLL5np1teDNcfw00cexWk9B+CjjZstnwlUyLhSrmSWE4adJRUWTVAGBw/leH9abKrBkDN6YPWaZ1ycgwmcACx/qNfBiGSVZ16fn3Hsaz1qD+zEqLNG46rrFmLP3kNozhTw6quvo6Gu3orYRGIFXB8t+G6wLAMnAf8pQd4ueeRu5Nk1KzF9+nS7qlB51uEfGpb7l8TxQ8fw3jyt+1uJl/zi3wAOyErwMjjRiiZyYR9uv/77mDrraphCltqCpsZjuODiy7Fg0WLLU4s1j9+PgaPPw35bhN999zWMGNADfTp3Qe+evbDqV6txuHAMQwePwIN3Poih/c5Az549ccNt96PRtvao245zBnbCiqefR4PV//Zbr2HkoH4Y0KUXTu/eH6t++TK+bMxg0tgpWGmTDlrqTaIspsyai3lLfup2H26iKhzAr1Y9iIEjx2NHrVWbfBpZKPAWIIuNVu/QXr2wac8RNFr8+7/+ABNGDkd97QH86c+/w7jxZ+MU63On3kNw532P4KXnV6J3l2+hR48eOOnkzvj5c8+4SWTxLT9CVc++qOryXcxfMBc76rLu7/L4/yzFtMljMX3WdPy/Tqdi6MixePDBhzFsSA9Udf02Jl14OXYf9bdLbvKwIPlFO0PZvSiVYHFuDjCpajfjwduuwZgL5sA/67cEk+m6G27GBVOnAY078dyyezFw3HRssQwzZ16IexZfDTQ0YNNnn2JrzXYczB3F0AGjcPXF1yBXdxQf/PZ9dOo7Cq+//aGV34aJg76Hh594BnVWe92xI9i7eSOQyWL50uUYOGEqtjUUcO9dP8Gs886zyeYYtu7cgh5DJ2DN6x/D3ZFT/vwe3D1/Ji647FocsDfBeL8z4ESWwcZX1qLvSSdhv90dsJ23X3sLU8aMQrbpCJbcOh9Tp45Hs92u/GHrUXy645AVPmyT3kxMvfAiv7IXs1iz6gn0HzIWn27di4baPTjv/LG44rb7cNiSVz7+M/SrOhkvrXsZh3IZjD13BgafOQybP3kbWzd/hG59z8KKF38Lm069/PZnlPyiXQnCk/S5O+HKT6kWXoZLrlmM/dzBJvJfu/CHuPCiGXZ+AI/etxhDx15kgx5YtfJxDDr9ZCxZvNBW1I1osuFeV2jAqIFj8YsVL9j9cz2aio0YP+0q3HPPI2biVkwafDL+5+kXwEeKzz//IiZWD0Dv//7f6HHaKThj3MXYY/Zt/HgDqvt0xZdb/ojVq1dj8LgZ2GvOl+XPsJ+X4/zZV4NPHpqsm/w0wj2Ys/SPX/8lhvaswqZdh93K/95b72LiqBGoqdmLd957BWcOqMKcK6/AWxv24qAz9AB+tOhiXPT9q9HAidBugebaqn7DLT/BUWvOZi088cSDGDR5OnZb/seXPoSxw/rjSKbG3jNw9YIf4trrfmBnNba72Ineg8bisafXVVZ+65bkF+1KK+GN8jlPeM9v2+kbr7oI0+bMg+2m7W7A7nWzjZhwwTTcsPgmmxz24efcbo+Y7KWxUX1w95/x8MP3ole/Hljxi+WozdTi7MHjserhNZaeMQGyOGfK93Gn3QagZivGD+6EpWuex8bd9RhWPQqP3H0rGr78I158ZhX6jZ6JXWZTPl+PKy8ah2UPPoDLL7sGS+5b7rbbXJWLzbaWF49gzaP3odegkdjHucDiK++lgA9f/gWGn94LB+ryNhnZ7cm69zBmZDXqG7huZ9B4bA+WPfE4ug2ZjFt/uhyo3457b5qFKTO+7yaYUmMN5l46DYvveBCNruIMli61HY/tTPZahlXLHsUF40diz+HdLv8NS+7Grbfebv06aIWPon/1eDyy8hUnv/vugVZ+0d5wHJclMSrnXOEpfw3uuWmerahznWxHDh3GzTcuxMChI/Dp5k1OuqeX/RT9R03AF7VFrF//e9SbAPmmOlx59WW48Z6FqCs1YIhte6+duQBHjuzBOx+8aZKejZdf/Q1w7EuMr+6Blb9Yi99u3ou+/QbhtReeReHoVsy7eibOPOdy7LGlupg/jOdX34cp50zCINtFvPOHz90K6/rrdihH3a6geswkXLnoDhyobUJTJo8/fvIZmmpr8clv3kDfbqfgxZfehiXhqivm4twJY3DY+vO7P7yLgwe/cE//F9/zKC6+3FbsllrcvfgKTJ1xCY422WxRyln7T2LgkDHYuOkL2/bvwwUXTMBVS+5CjXWCK//kMdU4VHfQpoUS5i9agiVLlli5GmTq96Hf4NF44uk3wU8eHZJftDcV+d3eNlwY3C5T/jrcev21+O/OPfB/O/dErx49MfuSi/HHTVuR4Td+bBv/9BMPYcSESdh2sBbXXXs1eld1QY+u3XDO+NHYuP0jHGzch7OHTMTCK5egX99e6NyzM2778VJkuWwX6jG496n42con3Yp585I7UNXpZAwb2Ml2Dz/CmClzsI9bDts+N9VsRp+eAzB+4mVotMWz0crnclaK237uUmyFf++jDbarmIFTulShc5duGDF8NI7ahGVLNxbNuwLdu/dDn77DcOftd2HyxLE2QRzFTx64G926d0a3nr0woHosXn7tHWsvg083/gann94bXbt1x7NPP4O8dfiW23+Mqu690b3rybh05nTsqLVdg+VeaSv/mOED0ZCtc58mzFt4E26/3VZ+2y9lGw+geuREPLbqV+45hPsmpJ1IftGu/EX5uXkuWCjmnZgMTOcv+rKW1T1Qo3jFBuRa8q6E/yzM7rktQ95yFCltdj+GnjESz6x4xeWhHM124trONFgbfM7v62Oc/959xq6zbpvMe/dSZrcVPIAJky/BQ4+/BH5Q4D7548eKxSbrk+udqyP0zdXPPAymaEu+DiWuvK4cAy+a7DLnHva5/rt420hYwZby36DZ1VdJ57cXGy2u4HYfbDnXZPsimyybrS8Ze+cur4OPFxtxrLHkPoHwfzML1gfJL9qVZKwbHK4WyhEUg2LbscBh7qV1v4ozwd21k8pe3Bdt+A06WwOT8kxnbK5lv+U4hMG9huLZp14vTyL8PN23w/LMnUepyBa8IOH7AS6L61cNXn1xNXrb9vmTbcfgvmPDOlyZvPvSDX96zGh+fdd94s4LC3nXUZOYgafsmMO9C8tifbd4XvlvGHHiSq5bODGZzpaeDTMKZxB7z+wf34t5bdfsYxbZfJOVS/rtJkIvf5g8/M+jbaJpzkp+0b4kfhgcmhzA7sLgtQ1yjlb7v7Ly2YXlKV87eOYlDBUyxqcfQWPDLgwfOAqrlv3S5aKGrplyW6zTUhgMLz8DTbFdR8MhjB5yOvr36YGnXnjdrbb8+N4lO+moui/joXyJgHxxJ6zbAjOxnOHz8Hv+/ks45fwWH/oQ3le5Gnfi+8X+uZ2Ji+d7OFFdftLhaTmeHbATyS/aDQ7EENwQdiL5CL+2p68ZOGj9wOVAZg4XF/Km8gd5Wty3A2z15FJqEczF1ZLZ+OKOjmRlNoIkrJv35C4+Yyuotc0cLF8qd8C3y/yM8mYzrrX8/v1YJ5jMYPjdBcsyLaGcPwRffyrC8G2wf0wtt+v6krRL3Eky6RjleJdX8ot2hIMxBD/AGfzA9KtiGj/gQx4OeD/oOeDbrLzlglwFG9GUb3DbdN5B8Lc/oe7w4x1PaoV1kWwvWUlLlpa0y9Y4jzj5mSXJ68rxNPTTHY0koTxJ8ZAk+ffAshYRKvCVOLzc/vbDxbNcOd2XYx0+KlScfk/E/31aw3ySX7QjHKStBy+37V6ckNYq3QnYOt2rWPnuuicM+Lz9l7V4W8Ftrx5+Dct/uYf/6g7rCCHU48R3gW1l7f49iwJNt/wl/grPklhNPmdnvqAjFPPvI4QK5ayVjO7g5U8uWCRJIzxtJT8bduksE4K/DvDaxwVCX9oGyS/akVbjmQOST7/dE3C/kgeFnbNuR0Ahme4HPeP5RN49DuR4dhkti4kc7qWD0BSfxyx/SWdx/M49f8LLehh8S1ZviOCDRXcPHdrnq5Ur+TiSS/1E13cgBCNkYp3JGWmbl7W6uBDVZnLjT3fK788Fvvh2Xb/KCb6eEwVPuQJ3xXjJL9oNDkAOYDeIy3Jz+12Rnw+0yumJkOnBXzDJW8vPE7+F9zXY1jn5aS6T+Zv6Ivf/SRrjmObbTkTlC6sx0dzEYedu629lmgu2l3CPzBl8na6Mq89PTA4XyXP21Y68Zl12cCt5Evx1+dLysJ7K+/8q+cvlkoSk+q8MPh9rrOSV/KLd4AAsD2I3Ijk4+UjNVle7pvheOYODntK6yaGyInMVD/e+Xgbmtjz+5t7i/H16Y67oP95jJotosdpzLc2VycV9quBacnU7VxjcRQHZ5nrXCvMzuH/00yaiSruhb0kRBytIJpqkLrbgf4jrGyiX5yVPUvLzMmcJLn9Id/K3fb+V/KFtHlvHsZxvN6RJftFutBqcbkRygHrdecmhWpYzrPyJTC4745PBzDwunxvklocHS6D8zZafJd0Kzq/DWUau5DYdVOp3kwsL+fpceQst/BahK22rrYmZsURelePs1feD/WorP0nkZ2RSlvfxvnLftsvPFzdhMQfTfRrlL9/zM9r9jSoC+8jK38DH+WM6+Hy+7hAn+UW74gdmgrvwg5mEQeqiW6X59DTlfA5LTyJCfDmtfEEJUhKVT9qQ5A1tVrL5uEqRSp7WtI6vlPeUz8sJx+d10a0uTsxfSEpo3T/JL0SkSH4hIkXyCxEpkl+ISJH8QkSK5BciUiS/EJEi+YWIFMkvRKRIfiEiRfILESmSX4hIkfxCRIrkFyJSJL8QkSL5hYgUyS9EpEh+ISJF8gsRKZJfiEiR/EJEiuQXIlIkvxCRIvmFiBTJL0SkSH4hIkXyCxEpkl+ISJH8QkSK5BciUiS/EJEi+YWIFMkvRKRIfiEiRfILESmSX4hIkfxCRIrkFyJSJL8QkSL5hYgUyS9EpEh+ISJF8gsRKZJfiEiR/EJEiuQXIlIkvxCRIvmFiBTJL0SkSH4hIkXyCxEpkl+ISJH8QkSK5BciUiS/EJEi+YWIFMkvRKRIfiEiRfILESmSX4hIkfxCRIrkFyJSJL8QkSL5hYgUyS9EpEh+ISJF8gsRKZJfiEiR/EJEiuQXIlIkvxCRIvmFiBTJL0SkSH4hIkXyCxEpkl+ISJH8QkSK5BciUiS/+A+h5EKLvTKUCREhMjmmo/4iIeMJCpwwyZ2wL4Gv6NcJqZRrVZ+FcvnyyYk5Ptm3T0KaTy9JftHBKY/mrB3yKNoZQxjg5fSW5Dzxq2gnDEGOkC1cuxDyh1DJ5A6hrVZtunYK5XxoybpgMS54WJkR6nN5GcccPq11feyrj3PXJcbxIiE556Fg5Rl8WQsl9iVvpyXk7JLB11OQ/KKDwwHuRrqXP0jmBjhxAvCYCOPOmU6hKrJRDgZ/HYIRrONlaMsCD6zj+PYsI0OA8qf65cszjz/11zwp5yhHpdN9N5K6Wb1LJL4uwjw5qyNncS6K+YpWp/WB75U9YXB9lfyiw5MM/BYb3BzgXhKvUVkAJ0siiTvn/2Hl99E8Zx3uwoKvy9JDZVxtee3KEp+fSQwen14OKcqTSyq9nM2d5O0lNUnw1QQN78fHMo9RLsj6Kn1iPop/opWf76X1yq9tv+jouFHOg5c5SEZV3CA3N1xI8pWv7SXIyCTK7uRP8lJrtzPgNSsL5Xl05z6hIqcRJphynvQl8zOkSOflrYJr1TfnRT9+MiDM7suwPV8v+xDy8rycp2jpNnHxb+NvCSppkl90bNxI9gPeSxhk8EIklxWYPxHGYdeMKsvv0nnN1dIEDFmTfCHdX/CQWmVdsMypSaAcnQqOr0wI+MmplchJnlbX7m1Qar/dD/nde2FI+u93Kf7eP6lG8osOTjKS/aBPpHMDPNm2B0HCiHcXjKfoPp1JXgovj6/Lr7xJYlkqt41299G8IJTMC+WiUvL7LJV6mZNHRzlf2/J88deVviQvFpKDhydWBetpK3/YOVQKsB0+F/G7FUZJfvEfQZDXjWo32CtP/1tCnIunLSZCiWLY0eJcdHLkPTG3x0EexlGb8r0yxS8mUhF3r57arrNouhsU3CJYng/bXB7i2mY9PtblD4VcGT95Mb9rN6mX55U6LILxdvQru2/H52f96X7xmg/+8sl7lPziP4bEjrI8jRb8QGfw8V4yR8mkM4l5XZbLLnx+XvjJgWlBFleW99Bu5WdbDG3kd+mVlTs0yLRW9YSyLRm78Pf2lX74WxD+F1ZzF28h1ONgHS4/X/xkwTSXny8WHdpEwV6L/Mgxj2a7ZLzkFx0aipQvb8NttJelamAKmiyeg72lwMmgETmTwq2wRZMu3+gccTJZsVIuPVkwwq6tLrcTYJlSiz/wyb8L1MpW51LRC0dKVq9NLMyXt5dMocV7yN2C5WVfmUZa8tazkvWz2Ox2BRlmzFudVp7iN1hPeCyyrC/u+sb34/CdMbF9IvXnBMDoXMb3OWd9dgXzVjLvV/6MJbA9yS86LBzWBROUR/dioZStt2Mj3ly7Bt06nYYHnljr9gBAnfm+DwOHjMUTy18w02owZ/oELLrlx2gwN8KkkTX3cnZaLJroLUVkS80mjKli4nD1pHhOvoLJlOwcOAWwnKuj6SDWrvoZevcbiu90G4jTzxyOnz78kKs731DDDjqZ6bmbhEzxlcuXYsDZU3DI5oGP316HAV1Owuf7d8LeiZOVOwEWYBearAk3ORmcrOg1223ONlhfrI0CdxIuO5oso2uCkyPjbcK798GfYFftMfceJL/osPj7fK6kFNdF2CCnYIcxb/ZFOHv4aEy+dAF213FlPIB80xcYMGgSVj/5lsv3/QtG4cY77neTQwvlyJt9VlWBgVW5KimY5SjU2TEPqx1HLCBn+lj+ZrPsWBDS7qmzW97H6F4n4ZlXf42DFvXBhs3Y+PEGK8u6rY4iV/o86kxkX6Yeyx97AAPHX4J9lvSnt9/CsKqT8af9O1xb2RzL5N0jCpuPXJkmTjyE75dvza3utp7bpGdbDOSyJbvyzxjcBONmtEM4uH09+g2pxraaZpcu+UUHpiI/x3+hmcP9EGp3r8fAHlV4ZvVa9Bg8Hht27LL43ZZ9H84YMBmrlr9hruzHrPPPxPV33oc9TcDRo0cwb84sdO/UBaf3GYQ77l2Kg8cymHvFbNxz8zzbOOzCb9a9hv/qPQL3PWPlbQVfvfQ+TJ15OQ7bBMCWueWv3fgmzun1Pax9830nv1Xt3cwfwVGTb8b4kejZpQt6nDEcTz73K+t0LVZzp3D2hdhj8m94+XWceep3sDdXg102Wbz2+q9QfUZ/nHpSN4wZdz42fr7NiTt//nzceMMizJl1Bao6d0HN/q249+6b0KdXb/TofQYGnzUam3budxMGJ7T6be9j0rAu+E5VH/yf7kMxYfocyS86MpQ+5+TnNtfd65vgy+6dj4lnjUFNbRHjps3G7Q/9xBbIL1HX+AWGDLkQz66wlb9xF+ZeNAyL73/ASbr45h9j2qTz0XBkH7Zt/jP6DxmHx1f8AiuXPoxLzh1mku7FrbcuxPjZN2D6NXdaO824atYFuPFH99sNha3KbL9gG/X6PXjolvno0bULZs29Hu/9eY+bAJDZg+unDsddN9/kdgvPrF2HwUPPQt3BHfjZ0h9hwLjpOGhWf/LGb3Gm3a7sOrYVn9V+7lbq1195x2aXEq6/bgFmXHmd26ncuHgRevfqgTdet7RiCTv/+D6G9T0Nb6x7224fsvjDhk+RsT9Ho4lfKtgMkNuODa88hlPOGIH1h/2kJPlFB8c/RMs4/cye7Be45vwRuOWm29BsA/+uBx5C9bhqk/+gbbUPYMCAc7Hm8Zct6y5cOX0o5i5ZYnsFYOTYC/HYI8tMYFt+bRKZv/AuXH7ZAmz/+EMM7/097Pj8Y0w6/1w8tfZtjJo4DTu3bsKw6kF49aONbnvuJx9b/3mLkK3F+ndex7njJ+CU/mdhxc9/icYtH+C807+N007phO9WDUCn7oPR6bRu+P27L2HF8gdw5sQZ+LIW2PjGH2zb3x07Dn+CB567H9/q1A1V3Qaiz2mdUXXaaRgxeRp21DVj/vU/wOxLZiDHp4pc3W0HsfCy89F34Ol4eMUq7D3YiCbrjns+wWcRdX/ClnVP4ZRBY7De2jlkfyrJLzo2Nq75tL3Z9C6hFtt//RyqT/4v9Oo+AN/t3A/f63YyuvX9Hn75znOot/vtgf3HY+3qV2yF3oGZk/tjwZ13upX/zLPPxcoVa0yUgi3qOSxccDvmXHqFLZG7MG5oNyxdvgYjJ12Eg4e+xLSpfGj4OIaMOBs7ajPuwRy3/Vn3VVruPsyshn1uIrjxdrs1uGgmmrb+DhP7d8KTT6/F7z+vwZat+7H3iy9sad7nVv7+46biiFXy0drfYODJp+JA8+dY8eYy9Bw0FJ9s2I4vN2/B7u3b8ckXB9xO44dLbsalMy70txQMzcdsBqrFZ59+iNlXzrHbnYnYsrfgt/1NNj3ltuFP61bjWz2HYou1U2fxkl90bGwQu7EPfix2FI/ePg+Xjq42Cb6we96D2LTtzxg/ZSQW3DIPx3IZDB482lb+1SbdHlx1yTgs+fF9OGC+LrZ7/ynnT8ORAwfx5fZdGDlsHJ74me0ESodw26LLMWrCxZh93a12XYf7756PcePG4bKrr3crK7fQDHz2tmvXLrz7mu0smo+guWYPLp51GX4w/3qbDPbjmhnn4tI5P8BuW3kbGkpY/+GHlq8GK5bZ7mTCVOypacH6Nz7CmV27YlfNJqzf+Tv0Hngmlj1mk1K+gMP79+H3n21z4i5cdAMuvWS6tWheNzWiWHcYn69/3+aAfdizb6971vHKe5+5B/3uIWNuFza89yJO6lONtzfVgM9AJb/o8BSL/vPtuvojGH9WNdYsf8w98eZWnM8DVj31BAYPGYBtO7bbVn0Unlz5FFpyDbhkxhQsuf0OJ+3R+iZcd90P0K1LV1R17YYbF92MHB8g2lb+1ZdewEmndsWTa35uO+gc3nnrZVRVVWHVk6v9zYaJ6dsCPv7Ytu3Vg9GrWydUdT4Vl86cjS1btlpiHnt3bsdFF89G1+59cZrVd+WcK5Cpr8Wqlcsw7KwxOHy0Ae+v+zUG9+uHXXu223SWxZtvrcPgQdXoYbcCXTt3wao1z7pPIRYvXowZ0y+wM/axGXu2bcE5I4a5h4lVXXvi+kW34ViDuxlBgbcyaMAxmxguvWIuvt25j+0OFkp+0bGh+H8r/JJOS4tNC/yCzF+A6SfKw7h8nlMG28+77wMwWIoL9fW2BU8I/SsUSshm7dYkqS6f9/HsRwjhOt1m+v2FNklzM/ccNg1wVedzBrtdcZ92cGaw4q47Dv+JSN5uRfzPlG0aaPbfKJT8osNTKNh9ugkTBArCBKmYniaX85+Th/y8Duc8piUL8SSbNbmM1hOOF75QyCUTQSV/23ZDGieCVLYybUVP9yXdp3BeKrI//ODPQoE7ADvk7IVdskMmk3X9sqnKQg65gn8sypCxP4HkFx2atoKlJUmvoBS8tbQ+PT1RpOsK8qXrSMtIfH6KzDr8JBAI+UKZdDli0S6QdD84wYR+hLi2/a7ASYdbeq74WVvtrX1OAJwTkrpdnmKzXfLbiAU05/gVXz8BSH7R4aEcFCaszBQuECRuS1rGUJ6cKG9TU1MrAcOEwCPF5+rKcpxg0pPFichk+E2849tIl2Pf0hMR84f0dF/ZruVOQgnN/GzPqi5ZV7P8MoGD5WzbX2hyKz+fgbB1fkog+UWHh0KlCdt6HoMojY38aoynraBp0YiX2sucTgvlGFeZPBjXur4A8zNvur20+IwO9bOttrcVYRJo299KHdZP/ojIgusDo5PgfutjlNyPj+xe3+Xx34YsFFvcjw8lv/iPgJJQoPSKHgiTAaVhCHl4nl7RTyQaYbx/wObLBJiX16FcSKPE6XwB5kuTzh8I7TMtnKf7FMr4Nitil+P51nhabp4rvd3zt/BvwPxJXZYu+UWHhoM+DPxAiEuL3TZfW6HSaSSUT8cHeUO96TrS54F0XCgT6gtpJyrXdpIg6UktpLMuPwH4OvggkVJz209Yt6+fwU8S7ufBBrsh+YXowHAqSYcTEyaAVJD8QnRsJL8QHY4gYvsh+YVoFyS/EJERpP9nyP+31S35hfiX8rcJ+vVpW28IX43kF+JfwonEZPiGlJ/0hfr40V/4+C9Vf5IvZGeQ/EL8Swgytg3fkGDy16xb8gshJL8QsSL5hYgUyS9EpEh+ISJF8gsRJcD/B6hgl2bK9XYzAAAAAElFTkSuQmCC';
            if (strlen($logo) > 1048576) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Logo image size too large.'));
            } else {
                $nodeLabel = $xml->addChild('Label', '', '');
                $nodeLabel->addChild('Logo', 'Y');
                $customerLogo = $nodeLabel->addChild('CustomerLogo', '', '');
                $customerLogo->addChild('LogoImage', $logo);
                $customerLogo->addChild('LogoImageFormat', strtoupper($extension));
                $nodeLabel->addChild('Resolution', '200');
            }
        }
        $requestData = $xml->asXML();
        if (!$requestData && !mb_detect_encoding($requestData) == 'UTF-8') {
            $requestData = utf8_encode($requestData);
        }
        $debugData = ['request' => $requestData];
        try {
            $client = $this->_httpClientFactory->create();
            $client->setUri((string) $this->_getGatewayUrl());
            $client->setConfig(['maxredirects' => 0, 'timeout' => 30]);
            $client->setRawData($requestData);
            $responseBody = $client->request(Request::METHOD_POST)->getBody();
            $responseBody = utf8_decode($responseBody);
            $debugData['result'] = $responseBody;
        } catch (\Exception $e) {
            $this->_errors[$e->getCode()] = $e->getMessage();
            $responseBody = '';
        }
        $this->_debug($debugData);

        return $responseBody;
    }
    
    /**
     * If admin allowed to display logo on label
     * @return array
     */
    protected function _getLogoImage()
    {
        $logoPath = '';
        $extension = 'JPG';
        $marketplaceHelper = $this->_objectManager->create('Magetop\Marketplace\Helper\Data');
        if ($this->getConfigData('dhl_logo_display')) {
            $logoPath = $marketplaceHelper->getMediaUrl().'dhl/logo/'.$this->getConfigData('dhl_logo');
            $extensionArray = explode('.', $logoPath??'');
            $extension = end($extensionArray);
        }

        if ($this->getConfigData('allow_seller_logo')) {
            $displaySellerLogo = $this->_customerSession->getCustomer()->getDhlLogo();
            if ($displaySellerLogo) {
                $seller = $marketplaceHelper->getSeller();
                $logoPath = $marketplaceHelper->getMediaUrl().'avatar/'.$seller['logo_pic'];
                $extensionArray = explode('.', $logoPath??'');
                $extension = end($extensionArray);
            } elseif ($this->getConfigData('dhl_logo_display')) {
                $logoPath = $marketplaceHelper->getMediaUrl().'dhl/logo/'.$this->getConfigData('dhl_logo');
                $extensionArray = explode('.', $logoPath??'');
                $extension = end($extensionArray);
            }
        }

        return [$logoPath, $extension];
    }
    
    /**
     * Add seller items details in request.
     * @param string                          $xml
     * @param  \Magento\Framework\DataObject $request
     * @param string                         $originRegionCode
     */
    public function _setItemsDetails($xml, $request, $originRegionCode)
    {
        $order = $this->_order;
        $customerId = $this->_customerSession->getCustomerId();
        $pieces = 0;
        foreach ($order->getAllItems() as $itemShipment) {
            if ($itemShipment->getProduct()->isVirtual() || $itemShipment->getParentItem()) {
                continue;
            }
            $collection = $this->_objectManager->create(
                'Magetop\Marketplace\Model\Products'
            )->getCollection()
            ->addFieldToFilter('product_id', ['eq' => $itemShipment->getProductId()])
            ->addFieldToFilter('user_id', $customerId);
            if (count($collection) > 0) {
                ++$pieces;
            }
        }

        $nodeShipmentDetails = $xml->addChild('ShipmentDetails', '', '');
        $nodeShipmentDetails->addChild('NumberOfPieces', count($pieces));
        $nodePieces = $nodeShipmentDetails->addChild('Pieces', '', '');

        if ($originRegionCode) {
            $nodeShipmentDetails->addChild(
                'CurrencyCode',
                $this->_storeManager->getStore()->getBaseCurrencyCode()
            );
        }

        $i = 0;
        $weight = 0;
        $salesListModel = [];
        $itemsDesc = [];
        $itemsQty = 0;
        $itemsQtyDiff = 0;
        $heightArr = array();
        $depthArr = array();
        $widthArr = array();
        
        $nodePiece = $nodePieces->addChild('Piece', '', '');
        $nodePiece->addChild('PieceID', 1);
        
        $packageType = 'EE';
        if ($this->getConfigData('content_type') == self::DHL_CONTENT_TYPE_NON_DOC) {
            $packageType = 'CP';
        }
        $nodePiece->addChild('PackageType', $packageType);
        
        foreach ($order->getAllItems() as $itemShipment) {
            $salesListModel = $this->_objectManager->create(
                'Magetop\Marketplace\Model\Saleslist'
            )
            ->getCollection()
            ->addFieldToFilter('sellerid', $customerId)
            ->addFieldToFilter('orderid', $order->getId())
            ->addFieldToFilter('prodid', $itemShipment->getProductId());
            //->addFieldToFilter('order_item_id', $itemShipment->getItemId());

            $unitweight = $itemShipment->getWeight() * $itemShipment->getQtyOrdered();

            if (count($salesListModel)) {
                if ($itemShipment->getHasChildren()) {
                    $_product = $this->_objectManager
                        ->create('Magento\Catalog\Model\Product')
                        ->load($itemShipment->getProductId());
                    if ($_product->getTypeId() == 'bundle') {
                        $childWeight = 0;
                        foreach ($itemShipment->getChildren() as $child) {
                            $productWeight = $this->_objectManager
                                ->create('Magento\Catalog\Model\Product')
                                ->load($child->getProductId())->getWeight();
                            $childWeight += $productWeight * $child->getQty();
                        }
                        $weight += $childWeight * $itemShipment->getQtyOrdered();
                    } elseif ($_product->getTypeId() == 'configurable') {
                        foreach ($itemShipment->getChildren() as $child) {
                            $productWeight = $this->_objectManager
                                ->create('Magento\Catalog\Model\Product')
                                ->load($child->getProductId())->getWeight();
                            $weight += $productWeight * $itemShipment->getQtyOrdered();
                        }
                    }
                } else {
                    $weight += $itemShipment->getWeight() * $itemShipment->getQtyOrdered();
                }
                $itemsQty += $itemShipment->getQtyOrdered();
                $itemsDesc[] = $itemShipment->getName();
                
                if($i==0)
                {
                    $height = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($itemShipment->getProductId())->getAlto();
                    $depth = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($itemShipment->getProductId())->getProfundidad();
                    $width = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($itemShipment->getProductId())->getAncho();
                    $heightArr[] = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($itemShipment->getProductId())->getAlto();
                    $depthArr[] = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($itemShipment->getProductId())->getProfundidad();
                    $widthArr[] = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($itemShipment->getProductId())->getAncho() * $itemShipment->getQtyOrdered();
                }else{  
                    //MAS DE UN PRODUCTO
                    $heightArr[] = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($itemShipment->getProductId())->getAlto();
                    $depthArr[] = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($itemShipment->getProductId())->getProfundidad();
                    $widthArr[] = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($itemShipment->getProductId())->getAncho() * $itemShipment->getQtyOrdered();
                }
                $i++;    
            }
        }//foreach
        $nodePiece->addChild('Weight', round($this->_getWeight($weight), 1));
        if($i==1){     
            if ($height && $depth && $width) {
                $nodePiece->addChild('Width', $width * $itemShipment->getQtyOrdered());
                $nodePiece->addChild('Height', $height);
                $nodePiece->addChild('Depth', $depth);
            }
        }else{
            $maxHeight=max($heightArr);
            $maxDepth=max($depthArr);
            $sumaWidth=array_sum($widthArr);
            if ($maxHeight && $maxDepth && $sumaWidth) {
                $nodePiece->addChild('Width', $sumaWidth);
                $nodePiece->addChild('Height', $maxHeight);
                $nodePiece->addChild('Depth', $maxDepth);
            }
        }

        if (!$originRegionCode) {
            $nodeShipmentDetails->addChild('Weight', round($this->_getWeight($weight), 1));
            $nodeShipmentDetails->addChild('WeightUnit', substr($this->_getWeightUnit(), 0, 1));
            $nodeShipmentDetails->addChild('GlobalProductCode', $this->_getServiceCode());
            $nodeShipmentDetails->addChild('LocalProductCode', $this->_getServiceCode());
            $nodeShipmentDetails->addChild('Date', $this->_coreDate->date('Y-m-d'));
            $nodeShipmentDetails->addChild('Contents', 'DHL Parcel');
            /*
             * The DoorTo Element defines the type of delivery service that applies to the shipment.
             * The valid values are DD (Door to Door), DA (Door to Airport) , AA and DC (Door to
             * Door non-compliant)
             */
            $nodeShipmentDetails->addChild('DoorTo', 'DD');
            $nodeShipmentDetails->addChild('DimensionUnit', substr($this->_getDimensionUnit(), 0, 1));
            if ($this->getConfigData('content_type') == self::DHL_CONTENT_TYPE_NON_DOC) {
                $packageType = 'CP';
            }
            $nodeShipmentDetails->addChild('PackageType', $packageType);
            if (
                $this->isDutiable(
                    $request->getShipperAddressCountryCode(),
                    $request->getRecipientAddressCountryCode()
                )
            ) {
                $nodeShipmentDetails->addChild('IsDutiable', 'Y');
            }
            $nodeShipmentDetails->addChild(
                'CurrencyCode',
                $this->_storeManager->getStore()->getBaseCurrencyCode()
            );
        } else {
            if ($this->getConfigData('content_type') == self::DHL_CONTENT_TYPE_NON_DOC) {
                $packageType = 'CP';
            }
            $nodeShipmentDetails->addChild('PackageType', $packageType);
            $nodeShipmentDetails->addChild('Weight', $this->_getWeight($weight));
            $nodeShipmentDetails->addChild('DimensionUnit', substr($this->_getDimensionUnit(), 0, 1));
            $nodeShipmentDetails->addChild('WeightUnit', substr($this->_getWeightUnit(), 0, 1));
            $nodeShipmentDetails->addChild('GlobalProductCode', $this->_getServiceCode());
            $nodeShipmentDetails->addChild('LocalProductCode', $this->_getServiceCode());

            /*
             * The DoorTo Element defines the type of delivery service that applies to the shipment.
             * The valid values are DD (Door to Door), DA (Door to Airport) , AA and DC (Door to
             * Door non-compliant)
             */
            $nodeShipmentDetails->addChild('DoorTo', 'DD');
            $nodeShipmentDetails->addChild('Date', $this->_coreDate->date('Y-m-d'));
            $nodeShipmentDetails->addChild('Contents', 'DHL Parcel TEST');
        }
    }
    
    /**
     * set recipent and seller data in object.
     */
    public function setShipemntRequest()
    {
        $request = $this->_rawRequest;
        $r = new \Magento\Framework\DataObject();
        $order = $this->_order;
        $customerId = $this->_customerSession->getCustomerId();

        //set default credentials
        $r->setAccessId($this->getConfigData('id'));
        $r->setPassword($this->getConfigData('password'));
        $r->setAccountNumber($this->getConfigData('account'));
        $r->setReadyTime($this->getConfigData('ready_time'));

        //set seller credentials
        $this->_isSellerHasOwnCredentials($r, $customerId);

        $destAddress = $order->getShippingAddress();

        //set Recipient Address
        $street = $destAddress->getStreet();
        $r->setRecipientAddressStreet1($street[0]);
        if (count($street) < 2) {
            $r->setRecipientAddressStreet2('');
        } else {
            $r->setRecipientAddressStreet2($street[1]);
        }
        $r->setRecipientContactPersonFullName($destAddress->getFirstname().' '.$destAddress->getLastname());
        $r->setRecipientAddressPostalCode($destAddress->getPostcode());
        $r->setRecipientAddressCity($destAddress->getCity());
        $region = $this->_region->create()->load($destAddress->getRegionId())->getCode();
        if ($region != '') {
            $r->setRecipientAddressStateOrProvinceCode($region);
        } else {
            $r->setRecipientAddressStateOrProvinceCode($destAddress->getCountryId());
        }
        $r->setRecipientAddressCountryCode($destAddress->getCountryId());
        $r->setRecipientContactPhoneNumber($destAddress->getTelephone());
        if ($destAddress->getCompany() != '') {
            $r->setRecipientContactCompanyName($destAddress->getCompany());
        } else {
            $r->setRecipientContactCompanyName($destAddress->getFirstname());
        }
        //Set Sender Address.

        $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($customerId);
        $address = $customer->getDefaultShipping();
        /* kien */
        $seller = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magetop\Marketplace\Model\Sellers')->getCollection()->addFieldToFilter('user_id',$customerId)->getFirstItem();
        $seller_data = $seller->getData();           
        $r->setShipperAddressStreet1($seller_data['address']);
        $r->setShipperAddressStreet2('');
        $r->setShipperContactPersonFullName($seller_data['name']);
        $r->setShipperAddressPostalCode($seller_data['zipcode']);
        $r->setShipperAddressCountryCode($seller_data['country']);
        $r->setShipperAddressCity($seller_data['city']);
        $r->setShipperContactPhoneNumber($seller_data['contactnumber']);
        $r->setShipperContactCompanyName($seller_data['company']);
        $r->setShipperAddressStateOrProvinceCode($seller_data['country']);
        /* kien */
        
        $totalPrice = 0;
        foreach ($order->getAllItems() as $itemShipment) {
            if ($itemShipment->getProduct()->isVirtual() || $itemShipment->getParentItem()) {
                continue;
            }
            $collection = $this->_objectManager->create(
                'Magetop\Marketplace\Model\Products'
            )->getCollection()
            ->addFieldToFilter('product_id', ['eq' => $itemShipment->getProductId()])
            ->addFieldToFilter('user_id', $customerId);

            if (count($collection) > 0) {
                $sellerOrders = $this->_objectManager->create(
                    'Magetop\Marketplace\Model\Saleslist'
                )->getCollection()
                ->addFieldToFilter('sellerid', ['eq' => $customerId])
                ->addFieldToFilter('orderid', ['eq' => $order->getId()]);

                foreach ($sellerOrders as $shippingPrice) {
                    $totalPrice += $itemShipment->getRowTotalInclTax() *
                    $itemShipment->getQtyOrdered() +
                    $shippingPrice->getShippingCharges();
                }
            } else {
                $totalPrice += $itemShipment->getRowTotalInclTax() *
                $itemShipment->getQtyOrdered()+$order->getShippingAmount();
            }
        }

        $r->setTotalValue($totalPrice);
        $this->setRawRequest($r);

        return $this;
    }
    
    /**
     * Is SellerDHLShipping Method.
     * @return boolean
     */
    protected function _isShippingMethod()
    {
        $shippingmethod = $this->_order->getShippingMethod();
        if (@strpos($shippingmethod, 'mpdhl') !== false) {
            return true;
        }

        return false;
    }
    
    /**
     * return service type for shipment.
     *
     * @return string
     */
    protected function _getServiceCode()
    {
        $shippingmethod = explode('mpdhl_', $this->_order->getShippingMethod()??'');

        return $shippingmethod[1];
    }

    public function proccessAdditionalValidation(\Magento\Framework\DataObject $request)
    {
        return true;
    }

    /**
     * get product weight
     * @param  object $item
     * @return int
     */
    protected function _getItemWeight($item)
    {
        $weight = 0;
        if ($item->getHasChildren()) {
            $_product = $this->_loadModel($item->getProductId(), 'Magento\Catalog\Model\Product');
            if ($_product->getTypeId() == 'bundle') {
                $childWeight = 0;
                foreach ($item->getChildren() as $child) {
                    $productWeight = $this->_loadModel(
                        $child->getProductId(),
                        'Magento\Catalog\Model\Product'
                    )->getWeight();
                    $childWeight += $productWeight * $child->getQty();
                }
                $weight = $childWeight * $item->getQty();
            } elseif ($_product->getTypeId() == 'configurable') {
                foreach ($item->getChildren() as $child) {
                    $productWeight = $this->_loadModel(
                        $child->getProductId(),
                        'Magento\Catalog\Model\Product'
                    )->getWeight();
                    $weight = $productWeight * $item->getQty();
                }
            }
        } else {
            $productWeight = $this->_loadModel(
                $item->getProductId(),
                'Magento\Catalog\Model\Product'
            )->getWeight();

            $weight = $productWeight * $item->getQty();
        }
	
        return $weight;
    }
    
    /**
     * load model
     * @param  int $id
     * @param  string $model
     * @return object
     */
    protected function _loadModel($id, $model)
    {
        return $this->_objectManager->create($model)->load($id);
    }
}