<?php
namespace Magetop\PaypalAdaptive\Block\Widget;
/**
 * Abstract class for this payment
 */
use \Magento\Framework\View\Element\Template;

class Redirect extends Template
{
    protected $Config;
    protected $_checkoutSession;
    protected $_customerSession;
    protected $_orderFactory;
    protected $_orderConfig;
    protected $httpContext;
    protected $_mkCoreOrder;
    //protected $_storeManager;
    protected $_saleslistFactory;
    protected $_mkProduct;
    protected $_mkSeller;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magetop\PaypalAdaptive\Model\PaypalAdaptive $paymentConfig,
        \Magento\Sales\Model\OrderFactory $mkCoreOrder,
        //\Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magetop\Marketplace\Model\SaleslistFactory $saleslistFactory,
        \Magetop\Marketplace\Model\ProductsFactory $mkProduct,
        \Magetop\Marketplace\Model\SellersFactory $mkSeller,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_checkoutSession = $checkoutSession;
        $this->_customerSession = $customerSession;
        $this->_orderFactory = $orderFactory;
        $this->_orderConfig = $orderConfig;
        $this->_isScopePrivate = true;
        $this->httpContext = $httpContext;
        $this->Config = $paymentConfig;
        $this->_mkCoreOrder = $mkCoreOrder;
        //$this->_storeManager = $storeManager;
        $this->_saleslistFactory = $saleslistFactory;
        $this->_mkProduct = $mkProduct;
        $this->_mkSeller = $mkSeller;
    }
    
    public function getLastOrderId(){
        return $this->_checkoutSession->getLastOrderId(); 
    }
    
    public function getOrderById($id){
         return $this->_mkCoreOrder->create()->load($id);
    }
    
    public function getCurrentCurrencyCode(){
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }
    
    public function getSellerIdByProduct($product_id){
        $mkProductModel = $this->_mkProduct->create();
        $mkProductCollection = $mkProductModel->getCollection()
                                              ->addFieldToFilter('product_id',$product_id)
                                              ->addFieldToFilter('status',1);
        $sellerId = 0;
		if(count($mkProductCollection))
		{
			foreach($mkProductCollection as $mkProductCollect)
			{
				$sellerId = $mkProductCollect->getUserId();
				break;
			}
		}
        return $sellerId;
    }
    
    public function getPaymentDetailSeller($seller_id){
        $mkSellerModel = $this->_mkSeller->create();
        $mkSellerCollection = $mkSellerModel->getCollection()
                                            ->addFieldToFilter('user_id',$seller_id)
                                            ->addFieldToFilter('is_vendor',1);
        $paymentsource = '';
		if(count($mkSellerCollection))
		{
			foreach($mkSellerCollection as $mkSellerCollect)
			{
				$paymentsource = $mkSellerCollect->getPaymentsource();
				break;
			}
		}
        return $paymentsource;
    }
    
    public function getSellerIdByEmailPaypal($email){
        $mkSellerModel = $this->_mkSeller->create();
        $mkSellerCollection = $mkSellerModel->getCollection()
                                            ->addFieldToFilter('paymentsource',$email)
                                            ->addFieldToFilter('is_vendor',1);
        $sellerId = '';
		if(count($mkSellerCollection))
		{
			foreach($mkSellerCollection as $mkSellerCollect)
			{
				$sellerId = $mkSellerCollect->getUserId();
				break;
			}
		}
        return $sellerId;
    }
    
    /**
	* get total commision fix 5/9/2106 for product custom option by kien magetop.com
	* return $item
	**/
    public function getTotalcommision($order_id,$product_id,$sellerid,$product_price){
        $saleItem = null;

		$saleslistModel = $this->_saleslistFactory->create();
		$collection = $saleslistModel->getCollection()
                        			 ->addFieldToFilter('orderid',$order_id)
                        			 ->addFieldToFilter('prodid',$product_id)
                        			 ->addFieldToFilter('sellerid',$sellerid)
                                     ->addFieldToFilter('proprice',$product_price);
		if(count($collection))
		{
			$saleItem = $collection->getFirstItem()->getTotalcommision();
		}
	
		return $saleItem;
    }
    
    /**
	* get actual parter procost fix 5/9/2020 for product custom option by kien magetop.com
	* return $item
	**/
    public function getActualparterprocost($order_id,$product_id,$sellerid,$product_price){
        $saleItem = null;

		$saleslistModel = $this->_saleslistFactory->create();
		$collection = $saleslistModel->getCollection()
			                         ->addFieldToFilter('orderid',$order_id)
                        			 ->addFieldToFilter('prodid',$product_id)
                        			 ->addFieldToFilter('sellerid',$sellerid)
                                     ->addFieldToFilter('proprice',$product_price);
		if(count($collection))
		{
			$saleItem = $collection->getFirstItem()->getActualparterprocost();
		}

		return $saleItem;
    }
}