<?php
/**
 * @author      Magetop
 * @package     Magetop_Api
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Api\Controller\Marketplace;

use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Magetop\Api\Helper\Data as DataHelper;

class GetSellerSales extends \Magetop\Api\Controller\AbstractController
{
    protected $_customerSession;
	protected $_resource;
	protected $_mkCoreOrder;
	protected $_priceHelper;
	protected $_orderAddess;
	protected $_saleslistFactory;
	protected $_country;
    protected $_objectmanager;
    protected $_shippingConfig;
    protected $_coreRegistry = null;
    
    public function __construct(
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Sales\Model\OrderFactory $mkCoreOrder,
		\Magento\Framework\Pricing\Helper\Data $priceHelper,
		\Magento\Sales\Model\Order\Address $orderAddess,
		\Magento\Directory\Model\Country $country,
		\Magetop\Marketplace\Model\SaleslistFactory $saleslistFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Shipping\Model\Config $shippingConfig,
        \Magento\Framework\Registry $registry,
        Context $context,
        EventManager $eventManager,
        AppEmulation $appEmulation,
        DataHelper $dataHelper
    ) {
        $this->_customerSession = $customerSession;
		$this->_resource = $resource;
		$this->_mkCoreOrder = $mkCoreOrder;
		$this->_priceHelper = $priceHelper;
		$this->_orderAddess = $orderAddess;
		$this->_country = $country;
		$this->_saleslistFactory = $saleslistFactory;
        $this->_objectmanager = $objectmanager; 
        $this->_shippingConfig = $shippingConfig;   
        $this->_coreRegistry = $registry;  
        parent::__construct($context, $eventManager, $appEmulation, $dataHelper);
    }
    
    /**
     * execute category list.
     *
     * @return \Magento\Framework\Controller\ResultFactory::TYPE_JSON
     */
    public function execute(){
        parent::execute();

        $responseData = [];
        $status = true;
        $message = 'Successfully!';
        $data = [];

        try{
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $customerSession = $objectManager->get('Magento\Customer\Model\Session');
            if($customerSession->isLoggedIn()) {
                $customerId = $customerSession->getCustomer()->getId();
            }
            $orders = $this->getSellerOrders();
            
            $ordersData = array();
            if(count($orders)){
                foreach($orders as $order){
                    $billingAddress = $order->getBillingAddress();
                    $shippingAddress = $order->getShippingAddress();
                    $totalAmount = $order->getTotalamount();
                    $ordersData[] = array(
                        'order-id' => __('#').$order->getIncrementId(),
                        'purchased-point' => 'Default Store View',
                        'purchased-date' => $order->getCreatedAt(),
                        'bill-to-name' => $billingAddress->getName(),
                        'ship-to-name' => $shippingAddress->getName(),
                        'grand-total' => $this->getMkPriceHelper()->currency($totalAmount,true,false),
                        'status' => strtolower( str_replace(' ','_',trim($order->getStatus()))),
                        'actions' => $order->getId()
                    );
                }
            }
            $data = array(
                'orders' => $ordersData
            );
        }catch(\Exception $e){
            $status = false;
            $message = $e->getMessage();
        }
        $responseData = $this->getResponseData($status, $message, $data);
        
        return $this->returnResultJson($responseData);
    }
    /* 
	* get List Sellers orders
	* @param int $cutomerId
	* return $items
	*/
	function getSellerOrders()
	{
		$customerSession = $this->_customerSession;
		$sellerid = $customerSession->getId();
		$orders = null;
		if($sellerid > 0)
		{
			//get all orders of seller
			$mkSalelistModel = $this->_saleslistFactory->create();
			$tableSalelist = $this->_resource->getTableName('multivendor_saleslist');
			$coreOrderModel = $this->_mkCoreOrder->create();
			$orders = $coreOrderModel->getCollection();
			$params = $this->getRequest()->getPost();
			if(count($params))
			{
				if(isset($params['order_id']) && $params['order_id'] != '')
				{
					$orderId = trim($params['order_id']);
					$orders->addFieldToFilter(
                        array('entity_id','increment_id'),
						array(
							array('eq'=>$params['order_id']),
							array('like'=>'%'.$params['order_id'].'%')
						)	
					);	
				}
				$fromDate = isset($params['from_date']) ? trim($params['from_date']) : '';
				$toDate = isset($params['to_date']) ? trim($params['to_date']) : '';
				if($fromDate != '' && $toDate == '')
				{
					$orders->addFieldToFilter('created_at',array('gteq'=>$fromDate));
				}
				elseif($fromDate == '' && $toDate != '')
				{
					$orders->addFieldToFilter('created_at',array('lteq'=>$toDate));
				}
				elseif($fromDate != '' && $toDate != '')
				{
					$orders->addFieldToFilter('created_at',array('gteq'=>$fromDate));
					$orders->addFieldToFilter('created_at',array('lteq'=>$toDate));
				}
				$orderStatus = isset($params['order_status']) ? trim($params['order_status']) : '';
				if($orderStatus != '')
				{
					$orders->addFieldToFilter('status',$orderStatus);
				}
			}
			$orders->getSelect()->joinLeft(
                array('mk_sales_list'=>$tableSalelist),'main_table.entity_id = mk_sales_list.orderid',
                array('total_commis'=>"SUM(totalcommision)",'totalamount'=>"SUM(totalamount)",'actualparterprocost'=>"SUM(actualparterprocost)",'sellerid')
            );
			$orders->getSelect()->where('mk_sales_list.sellerid=?',$sellerid);
			$orders->getSelect()->group('main_table.entity_id');
            $orders->setOrder('id','DESC');
			$limit = $this->getRequest()->getParam('limit',5);
			if($limit > 0)
			{
				$orders->setPageSize($limit);
			}
			$curPage = $this->getRequest()->getParam('p',1);
			if($curPage > 1)
			{
				$orders->setCurPage($curPage);
			}
		}
		return $orders;
	}
    protected function _prepareLayout()
    {
        $collection = $this->getSellerOrders();
        parent::_prepareLayout();
        if ($collection) {
            // create pager block for collection
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager','my.custom.pager');
            $pager->setAvailableLimit(array(5=>5,10=>10,20=>20,'30'=>'30')); 
            $pager->setCollection($collection);
            $this->setChild('pager', $pager);
            $collection->load();
        }
        return $this;
    }
    /**
     * @return method for get pager html
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }   
	/**
	* get Price Helper
	* return \Magento\Framework\Pricing\Helper\Data
	**/
	function getMkPriceHelper()
	{
		return $this->_priceHelper;
	}
	/** 
	* get data from saleslist model
	**/
	function getSalelist($orderId,$porductId,$assignProduct)
	{
		$saleItem = null;
		$customerSession = $this->_customerSession;
		if($customerSession->isLoggedIn())
		{
			$sellerid = $customerSession->getId();
			if($sellerid > 0)
			{
				$saleslistModel = $this->_saleslistFactory->create();
				$collection = $saleslistModel->getCollection()
					->addFieldToFilter('orderid',$orderId)
					->addFieldToFilter('prodid',$porductId)
					->addFieldToFilter('sellerid',$sellerid);
                    $moduleManager = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Framework\Module\Manager');
                    //Assign product  
                    if($moduleManager->isEnabled('Magetop_SellerAssignProduct')){
                        $collection->addFieldToFilter('multivendor_assign_product_id',$assignProduct);
                    }  
                    //end Assign product  
				if(count($collection))
				{
					$saleItem = $collection->getFirstItem();
				}
			}
		}
		return $saleItem;
	}
	/**
	* get Current Customer Id
	**/
	function getMkCurrentCustomerId()
	{
		$customerSession = $this->_customerSession;
		$sellerid = $customerSession->getId();
		return $sellerid;
	}
	/**
	* get detail order
	* return $item
	**/
	function getMkDetailOrder()
	{
		$orderId = $this->getRequest()->getParam('order_id',0);
		$order = null;
		$customerSession = $this->_customerSession;
		$customerId = $customerSession->getId();
		if($customerSession->isLoggedIn())
		{
			if($orderId > 0)
			{
				$collection = $this->getSellerOrders();
				$collection->addFieldToFilter('entity_id',$orderId);
				if(count($collection))
				{
					foreach($collection as $collect)
					{
						if($collect->getSellerid() == $customerId)
						{
							$order = $collect;
							break;
						}
						
					}
				}
			}
		}
		return $order;
	}
    /**
	* get total commision fix 5/9/2106 for product custom option by kien magetop.com
	* return $item
	**/
    public function getTotalcommision($order_id,$product_id,$product_price){
        $saleItem = null;
		$customerSession = $this->_customerSession;
		if($customerSession->isLoggedIn())
		{
			$sellerid = $customerSession->getId();
			if($sellerid > 0)
			{
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
			}
		}
		return $saleItem;
    }
    /**
	* get actual parter procost fix 9/2/2020 for product custom option by kien magetop.com
	* return $item
	**/
    public function getActualparterprocost($order_id,$product_id,$product_price){
        $saleItem = null;
		$customerSession = $this->_customerSession;
		if($customerSession->isLoggedIn())
		{
			$sellerid = $customerSession->getId();
			if($sellerid > 0)
			{
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
			}
		}
		return $saleItem;
    }
	/**
	* get order address
	* return $item
	**/
	function getMkOrderAdderess($addressId)
	{
		$addressModel = $this->_orderAddess;
		return $addressModel->load($addressId);
	}
	function getBkCountryName($code)
	{
		$country = $this->_country->loadByCode($code);
		$name = '';
		if($country->getId())
		{
			$name = $country->getName();
		}
		return $name;
	}
    /**
     * @return array
     */
    public function getBackUrl(){
        $url = $this->getUrl('marketplace/seller/vieworder', array('order_id'=>$this->getRequest()->getParam('order_id')));
        return $url;
    }
    /**
     * Retrieve shipment model instance
     *
     * @return \Magento\Sales\Model\Order\Shipment
     */
    public function getShipment()
    {
        return $this->_coreRegistry->registry('current_shipment');
    }
    /**
     * @return array
     */
    protected function _getCarriersInstances()
    {
        return $this->_shippingConfig->getAllCarriers();
    }
    /**
     * Retrieve carriers
     *
     * @return array
     */
    public function getCarriers()
    {
        $carriers = [];
        $carrierInstances = $this->_getCarriersInstances();
        $carriers['custom'] = __('Custom Value');
        foreach ($carrierInstances as $code => $carrier) {
            if ($carrier->isTrackingAvailable()) {
                $carriers[$code] = $carrier->getConfigData('title');
            }
        }
        return $carriers;
    }
}