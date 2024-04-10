<?php
namespace Magetop\SellerDHLShipping\Helper;

/**
 * SellerDHLShipping data helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var string
     */
    protected $_code = 'mpdhl';
    /**
     * Core store config.
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param Magento\Framework\App\Helper\Context        $context
     * @param Magento\Catalog\Model\ResourceModel\Product $product
     * @param Magento\Store\Model\StoreManagerInterface   $_storeManager
     * @param Magento\Directory\Model\Currency            $currency
     * @param Magento\Framework\Locale\CurrencyInterface  $localeCurrency
     * @param \Magento\Customer\Model\Session             $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context);
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
    }
    
    /**
     * Retrieve information from carrier configuration.
     *
     * @param string $field
     *
     * @return void|false|string
     */
    public function getConfigData($field)
    {
        if (empty($this->_code)) {
            return false;
        }
        $path = 'carriers/'.$this->_code.'/'.$field;

        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()
        );
    }
    /**
     * check if label content exists then show print button
     * @param int  $orderId
     * @return boolean
     */
    public function _isDhlShipment($orderId)
    {
        $customerId = $this->_customerSession->getCustomerId();
        $order = $this->_objectManager->create('Magento\Sales\Model\Order')->load($orderId);
        $shippingmethod = $order->getShippingMethod();

        if (@strpos($shippingmethod, 'mpdhl') !== false) {
            $sellerOrders = $this->_objectManager->create(
                'Magetop\Marketplace\Model\Saleslist'
            )->getCollection()
            ->addFieldToFilter('sellerid', ['eq' => $customerId])
            ->addFieldToFilter('orderid', ['eq' => $orderId]);

            $labelContent = '';
            foreach ($sellerOrders as $row) {
                $labelContent = $row->getShipmentLabel();
            }

            if ($labelContent != '') {
                return true;
            }
        } elseif (@strpos($shippingmethod, 'mp_multishipping') !== false) {
            $sellerOrders = $this->_objectManager->create(
                'Magetop\Marketplace\Model\Saleslist'
            )->getCollection()
            ->addFieldToFilter('sellerid', ['eq' => $customerId])
            ->addFieldToFilter('orderid', ['eq' => $orderId]);

            $labelContent = '';
            $method = '';
            foreach ($sellerOrders as $row) {
                $labelContent = $row->getShipmentLabel();
                $method = $row->getMultishipMethod();
            }

            if ($labelContent != '' && @strpos($method, 'mpdhl') !== false) {
                return true;
            }
        }
        return false;
    }
}