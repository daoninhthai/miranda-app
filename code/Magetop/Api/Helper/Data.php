<?php
/**
 * @author      Magetop
 * @package     Magetop_Api
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Api\Helper;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Validator\EmailAddress;
use Magento\Framework\Validator\ValidatorChain;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	const XML_PATH_ENABLED          = 'magetop_api/general/enabled';
    const XML_PATH_DEBUG            = 'magetop_api/general/debug';
    const XML_MAGETOP_API_IS_ACTIVE = 'magetop_api_system/general/magetop_api_is_active';
    const XML_DEFAULT_CATEGORY_ID = 'magetop_api_system/general/default_cateogry_id';
    const XML_HIDDEN_CATEGORY_IDS = 'magetop_api_system/general/hidden_category_ids';
    const XML_PRODUCTS_PER_PAGE = 'magetop_api_system/general/products_per_page';
    const XML_QR_CODE_SEARCH_IS_ACTIVE = 'magetop_api_system/general/qr_code_search_is_active';
    const XML_SHOW_TERMS_N_CONDITIONS = 'magetop_api_system/terms_n_conditions/show_terms_n_conditions';
    const XML_TERMS_N_CONDITIONS_TITLE = 'magetop_api_system/terms_n_conditions/terms_n_conditions_title';
    const XML_TERMS_N_CONDITIONS_CONTENT = 'magetop_api_system/terms_n_conditions/terms_n_conditions_content';

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $_logger;

	/**
	 * @var \Magento\Framework\Module\ModuleListInterface
	 */
	protected $_moduleList;

	/**
	 * @var \Magento\Framework\App\ObjectManager
	 */
	protected $_objectManager;
    
    /**
     * @var \Magento\Payment\Helper\Data
     */
    protected $_paymentHelper;
    
    /**
     * @var \Magento\Shipping\Model\CarrierFactory
     */
    protected $_carrierFactory;

	/**
	 * @param \Magento\Framework\App\Helper\Context $context
	 * @param \Magento\Framework\Module\ModuleListInterface $moduleList
	 */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context, 
		\Magento\Framework\Module\ModuleListInterface $moduleList,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Shipping\Model\CarrierFactory $carrierFactory
	) {
		$this->_logger = $context->getLogger();
		$this->_moduleList = $moduleList;
		$this->_storeManager = $storeManager;
		$this->_objectManager = ObjectManager::getInstance();
        $this->_paymentHelper = $paymentHelper;
        $this->_carrierFactory = $carrierFactory;        
		parent::__construct($context);
	}

	/**
	 * Check if enabled
	 *
	 * @return string|null
	 */
	public function isEnabled()
	{
		return $this->scopeConfig->getValue(
			self::XML_PATH_ENABLED,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	public function getDebugStatus()
	{
		return $this->scopeConfig->getValue(
			self::XML_PATH_DEBUG,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}
	
	public function getMediaUrl() {
		$baseUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
		return $baseUrl;
	}
	
	public function getExtensionVersion()
	{
		$moduleCode = 'Magetop_Api';
		$moduleInfo = $this->_moduleList->getOne($moduleCode);
		return $moduleInfo['setup_version'];
	}

	/**
	 *
	 * @param $message
	 * @param bool|false $useSeparator
	 */
	public function log($message, $useSeparator = false)
	{
		if ($this->getDebugStatus()) {
			if ($useSeparator) {
				$this->_logger->addDebug(str_repeat('=', 100));
			}
			$this->_logger->addDebug($message);
		}
	}

	/**
	 * Get Configuration Data
	 *
	 * @param string $configName
	 */
	public function getSetting($configName)
    {
        if (!$configName) return null;
        return $this->scopeConfig->getValue($configName, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
	}
	
	/**
	 * 
	 *
	 * @param array $object
	 * @param array $arrTypes
	 *
	 * @return array
	 */
	 public function formatObjectData($object, $formattingCondition){
		$formattedObjectData = [];
		foreach($formattingCondition as $index=>$type){
			$value = $object->getData($index);
			settype($value, $type);
			$formattedObjectData[$index] = $value;
		}
		return $formattedObjectData;
	}

	/**
	 *
	 * @param string $email
	 *
	 * @return bool
	 */
	public function validateEmail($email){
		return ValidatorChain::is($email, EmailAddress::class);
	}

	/**
	 * Get Current Store ID
	 *
	 * @return int
	 */
	public function getCurrentStoreId(){
		return (int) $this->_storeManager->getStore()->getId();
	}

	/**
	 * Get Current Website ID
	 *
	 * @return int
	 */
	 public function getCurrentWebsiteId(){
		return (int) $this->_storeManager->getStore()->getWebsiteId();
	}

	/**
	 * Get Default Country ID
	 *
	 * @return string
	 */
	public function getDefaultCountryId(){
		return $this->getSetting('general/country/default');
    }

    /**
     * getMaxQueryLength max search query length.
     *
     * @return int
     */
    public function getMaxQueryLength()
    {
        return (int) $this->getSetting(\Magento\Search\Model\Query::XML_PATH_MAX_QUERY_LENGTH);
    }

    /**
	 * Get magetop api is active
	 *
	 * @return boolean
	 */
	public function getMagetopApiIsActive(){
		return (boolean) $this->getSetting(self::XML_MAGETOP_API_IS_ACTIVE);
    }

    /**
	 * Get default category id
	 *
	 * @return int
	 */
	public function getDefaultCategoryId(){
		return (int) $this->getSetting(self::XML_DEFAULT_CATEGORY_ID);
    }

    /**
	 * Get hidden category ids
	 *
	 * @return string
	 */
	public function getHiddenCategoryIds(){
		return (string) $this->getSetting(self::XML_HIDDEN_CATEGORY_IDS);
    }

    /**
	 * Get Products Per Page
	 *
	 * @return int
	 */
	public function getProductsPerPage(){
		return (int) $this->getSetting(self::XML_PRODUCTS_PER_PAGE);
    }

    /**
	 * Get Quick Response Code Search Is Active
	 *
	 * @return boolean
	 */
	public function getQuickResponseCodeSearchIsActive(){
		return (boolean) $this->getSetting(self::XML_QR_CODE_SEARCH_IS_ACTIVE);
    }

    /**
	 * Get Show Terms And Conditions
	 *
	 * @return boolean
	 */
	public function getShowTermsAndConditions(){
		return (boolean) $this->getSetting(self::XML_SHOW_TERMS_N_CONDITIONS);
    }

    /**
	 * Get Terms And Conditions Title
	 *
	 * @return string
	 */
	public function getTermsAndConditionsTitle(){
		return (string) $this->getSetting(self::XML_TERMS_N_CONDITIONS_TITLE);
    }

    /**
	 * Get Terms And Conditions Content
	 *
	 * @return string
	 */
	public function getTermsAndConditionsContent(){
		return (string) $this->getSetting(self::XML_TERMS_N_CONDITIONS_CONTENT);
    }

    /**
	 * Get root category id
	 *
	 * @return int
	 */
    public function getRootCategoryId(){
        return (int) $this->_storeManager->getStore()->getRootCategoryId();
    }

    /**
	 * Get Is Guest Allow To Write
	 *
	 * @return boolean
	 */
	public function getIsGuestAllowToWrite(){
		return (boolean) $this->getSetting('catalog/review/allow_guest');
    }

    /**
	 * Get All general api app configurations
	 *
	 * @return array
	 */
	public function getApiAppConfigurations(){
        $configs = [];
        $configs['magetop_api_is_active'] = $this->getMagetopApiIsActive();
        $configs['default_cateogry_id'] = $this->getDefaultCategoryId();
        $configs['hidden_category_ids'] = $this->getHiddenCategoryIds();
        $configs['products_per_page'] = $this->getProductsPerPage();
        $configs['qr_code_search_is_active'] = $this->getQuickResponseCodeSearchIsActive();
        $configs['show_terms_n_conditions'] = $this->getShowTermsAndConditions();
        $configs['terms_n_conditions_title'] = $this->getTermsAndConditionsTitle();
        $configs['terms_n_conditions_content'] = $this->getTermsAndConditionsContent();
		return $configs;
    }
    
    public function limit_text($text, $limit) {
        if (str_word_count($text, 0) > $limit) {
            $words = str_word_count($text, 2);
            $pos = array_keys($words);
            $text = substr($text, 0, $pos[$limit]) . '...';
        }
        return $text;
    }

    /**
     * getAttributeInputType.
     *
     * @param object $attribute
     *
     * @return string input type
     */
    public function getAttributeInputType($attribute)
    {
        $dataType = $attribute->getBackend()->getType();
        $inputType = $attribute->getFrontend()->getInputType();
        
        switch($inputType){
            case 'select':
            case 'multiselect':
                return 'select';
            case 'boolean':
                return 'boolean';
            case 'price':
                return 'price';
        }
        switch($dataType){
            case 'int':
            case 'decimal':
                return 'number';
            case 'datetime':
                return 'date';
        }
        
        return 'text';
    }
	
	public function formatMyOrdersData( \Magento\Sales\Model\Order $order ){
		$formattingCondition = [
			'entity_id'=>'int',
			'increment_id'=>'string',
			'state'=>'int',
			'status'=>'string',
			'order_key'=>'int',
			'number'=>'int',
			'store_currency_code'=>'string',
			'shipping_amount'=>'int',
			'shipping_tax_amount'=>'int',
			'subtotal'=>'int',
			'grand_total'=>'int',
			'tax_amount'=>'int',
			'customer_note'=>'int',
			'created_at'=>'string',
			'updated_at'=>'string',
			'customer_id'=>'int',
			'discount_amount'=>'int',
			'shipping_address_id'=>'int',
			'shipping_description'=>'string'
		];
		$data = $this->formatObjectData($order, $formattingCondition);
		$data['shipping_amount'] = $this->formatPrice($data['shipping_amount']);
		$data['shipping_tax_amount'] = $this->formatPrice($data['shipping_tax_amount']);
		$data['subtotal'] = $this->formatPrice($data['subtotal']);
		$data['grand_total'] =  $this->formatPrice($data['grand_total']);
		$data['tax_amount'] =  $this->formatPrice($data['tax_amount']);
		$data['discount_amount'] =  $this->formatPrice($data['discount_amount']);
		
		$data['billing'] = $order->getBillingAddress()->getData();
		$data['shipping'] = $order->getShippingAddress()->getData();
		
		$items =$order->getAllItems();
		foreach ($items as $item) {		
			//$prod_item[] = $item->getData();
			$prod_item[] = [
                'id' => (int)$item->getQuoteItemId(),
                'name' => $item->getName(),
                'sku' => $item->getSku(),
                'product_id' => (int)$item->getProductId(),
                'qty_ordered' => (int)$item->getQtyOrdered(),
                'price' => $this->formatPrice($item->getPrice()),
                'row_total' => $this->formatPrice($item->getRowTotal()),
                'tax_amount' => $this->formatPrice($item->getTaxAmount())
            ];
		}
		$data['prod_item'] = $prod_item;
		
		return $data;
	}
	public function formatPrice( $price ) {
		return strip_tags($this->_objectManager
                            ->create('Magento\Framework\Pricing\Helper\Data')
                            ->currency( $price ));
	}
	/**
     * format magento shipping method to wc shipping method
     *
     * @param $method
     * @return array
     */
    public function formatShippingMethod($method)
    {
        /* @var $method \Magento\Quote\Model\Quote\Address\Rate */
        return [
            'min_amount' => 0,
            'requires' => '',
            'supports' => [],
            'carrier_id' => $method->getCarrier(),
            'id' => $method->getCode(),
            'method_title' => $method->getMethodTitle(),
            'method_description' => '',
            'enable' => 'yes',
            'title' => $method->getCarrierTitle(),
            'rates' => [],
            'tax_status' => 'taxable',
            'fee' => null,
            'cost' => $this->formatPrice($method->getPrice()),
            'minimum_fee' => null,
            'instance_id' => null,
            "instance_form_fields" => [],
            "instance_settings" => [
                "title" => $method->getMethodTitle(),
                "requires" => "",
                "min_amount" => 0
            ],
            "availability" => null,
            "countries" => [],
            "plugin_id" => "",
            "errors" => [],
            "settings" => [],
            "form_fields" => [],
            "method_order" => 1,
            "has_settings" => true
        ];
    }
    
    /**
     * format order
     *
     * @param \Magento\Sales\Model\Order $order
     * @return array
     */
    public function formatOrderObjectDetail(\Magento\Sales\Model\Order $order) {
        $formattedItems = [];
        $items =$order->getAllItems();
        foreach ($items as $item) {
            $formattedItems[] = [
                'id' => (int)$item->getQuoteItemId(),
                'name' => $item->getName(),
                'sku' => $item->getSku(),
                'product_id' => $item->getProductId(),
                'variation_id' => $item->getProductId(),
                'quantity' => $item->getQtyOrdered(),
                'tax_class' => '',
                'price' => $this->formatPrice($item->getPrice()),
                'subtotal' => $this->formatPrice($item->getRowTotal()),
                'subtotal_tax' => $this->formatPrice($item->getTaxAmount()),
                'total' => $this->formatPrice($item->getRowTotal()),
                'total_tax' => $this->formatPrice($item->getTaxAmount()),
                'taxes' => [],
                'meta' => []
            ];
        }

        $billing = $order->getBillingAddress();
        $shipping = $order->getShippingAddress();
        $payment = $order->getPayment();

        /* @var $paymentMethod \Magento\Payment\Model\MethodInterface */
        $paymentMethod = $this->_paymentHelper->getMethodInstance($payment->getMethod());

        /* @var $shippingMethodInfo \Magento\Framework\DataObject */
        $shippingMethodInfo = $order->getShippingMethod(true);
        /* @var $shippingMethod \Magento\Shipping\Model\Carrier\AbstractCarrier */
        $shippingMethod = $this->_carrierFactory->get($shippingMethodInfo->getData('carrier_code'));

        return [
            'id' => (int)$order->getId(),
            'parent_id' => 0,
            'status' => $order->getStatus(),
            'order_key' => $order->getIncrementId(),
            'number' => $order->getIncrementId(),
            'currency' => $order->getOrderCurrencyCode(),
            'date_created' => $order->getCreatedAt(),
            'date_modified' => $order->getUpdatedAt(),
            'customer_id' => $order->getCustomerId(),

            'discount_total' => $this->formatPrice(abs($order->getDiscountAmount())),
            'discount_tax' => $this->formatPrice(abs($order->getDiscountTaxCompensationAmount())),
            'shipping_total' => $this->formatPrice($order->getShippingAmount()),
            'shipping_tax' => $this->formatPrice($order->getShippingTaxAmount()),
            'cart_tax' => $this->formatPrice($order->getTaxAmount()),
            'total' => $this->formatPrice($order->getGrandTotal()),
            'payment_total' => $order->getGrandTotal(),
            'total_tax' => $this->formatPrice($order->getTaxAmount()),

            'billing' => [
                'first_name' => $billing->getFirstname(),
                'last_name' => $billing->getLastname(),
                'company' => $billing->getCompany(),
                'address_1' => $billing->getStreetLine(1),
                'address_2' => $billing->getStreetLine(2),
                'city' => $billing->getCity(),
                'state' => $billing->getRegionId(),
                'postcode' => $billing->getPostcode(),
                'country' => $billing->getCountryId(),
                'email' => $billing->getEmail(),
                'phone' => $billing->getTelephone()
            ],

            'shipping' => [
                'first_name' => $shipping->getFirstname(),
                'last_name' => $shipping->getLastname(),
                'company' => $shipping->getCompany(),
                'address_1' => $shipping->getStreetLine(1),
                'address_2' => $shipping->getStreetLine(2),
                'city' => $shipping->getCity(),
                'state' => $shipping->getRegionId(),
                'postcode' => $shipping->getPostcode(),
                'country' => $shipping->getCountryId(),
                'email' => $shipping->getEmail(),
                'phone' => $shipping->getTelephone()
            ],

            'payment_method' => $payment->getMethod(),
            'payment_method_title' => $paymentMethod->getTitle(),
            'transaction_id' => '',
            'customer_note' => '',
            'line_items' => $formattedItems,
            'tax_lines' => [],
            'shipping_lines' => [
                [
                    'id' => '',
                    'method_title' => $shippingMethod->getConfigData('title'),
                    'method_id' => $shippingMethodInfo->getData('method'),
                    'total' => $this->formatPrice($order->getShippingAmount()),
                    'total_tax' => $this->formatPrice($order->getShippingTaxAmount()),
                    'taxes' => []
                ]
            ],
            'fee_lines' => [],
            'coupon_lines' => [
                [
                    'id' => '',
                    'code' => $order->getCouponCode(),
                    'discount' => '',
                    'discount_tax' => ''
                ]
            ],
            'refunds' => []
        ];
    }
}