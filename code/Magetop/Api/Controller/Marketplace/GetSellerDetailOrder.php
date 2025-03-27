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

class GetSellerDetailOrder extends \Magetop\Api\Controller\AbstractController
{
    protected $_customerSession;
	protected $_resource;
	protected $_mkCoreOrder;
	protected $_priceHelper;
	protected $_orderAddess;
	protected $_country;
    protected $_saleslistFactory;
    protected $_transactionsFactory;    
    protected $_partnerFactory;   
    protected $_reviewsFactory;
    protected $_productsFactory;
    
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Sales\Model\OrderFactory $mkCoreOrder,
		\Magento\Framework\Pricing\Helper\Data $priceHelper,
		\Magento\Sales\Model\Order\Address $orderAddess,
		\Magento\Directory\Model\Country $country,
		\Magetop\Marketplace\Model\SaleslistFactory $saleslistFactory,
        \Magetop\Marketplace\Model\TransactionsFactory $transactionsFactory,  
        \Magetop\Marketplace\Model\PartnerFactory $partnerFactory,    
        \Magetop\Marketplace\Model\ReviewsFactory $reviewsFactory,
		\Magento\Catalog\Model\ProductFactory $productsFactory,
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
        $this->_transactionsFactory = $transactionsFactory;
        $this->_partnerFactory = $partnerFactory;  
        $this->_reviewsFactory = $reviewsFactory;
        $this->_productsFactory = $productsFactory;
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
            $order = $this->getMkDetailOrder();
            $moduleManager = $objectManager->create('Magento\Framework\Module\Manager');
            
            $customer_group = null;
            if($order->getCustomerGroupId()){
                $groupOptions = $objectManager->get('\Magento\Customer\Model\Group')->load($order->getCustomerGroupId());
                $customer_group = $groupOptions->getData('customer_group_code');
            }
            
            $billing_address = null;
            if($order->getBillingAddress()){
                $billdingAddress = $this->getMkOrderAdderess($order->getBillingAddress()->getId());
                $billing_address .= $billdingAddress->getFirstname() .' '.$billdingAddress->getLastname().'<br/>';
                $billing_address .= $billdingAddress->getCompany().'<br/>';
                $billing_address .= $billdingAddress->getData('street').'<br/>';
                $billing_address .= $billdingAddress->getCity() .', '. $billdingAddress->getRegion() .', '. $billdingAddress->getPostcode().'<br/>';
                $billing_address .= $this->getBkCountryName($billdingAddress->getCountryId()).'<br/>';
                $billing_address .= __('T:') . $billdingAddress->getTelephone();
            }
            
            $shipping_address = null;
            if($order->getShippingAddress()){
                $shippingAddress = $this->getMkOrderAdderess($order->getShippingAddress()->getId());
                $shipping_address .= $shippingAddress->getFirstname() .' '.$shippingAddress->getLastname().'<br/>';
                $shipping_address .= $shippingAddress->getCompany().'<br/>';
                $shipping_address .= $shippingAddress->getData('street').'<br/>';
                $shipping_address .= $shippingAddress->getCity() .', '. $shippingAddress->getRegion() .', '. $shippingAddress->getPostcode().'<br/>';
                $shipping_address .= $this->getBkCountryName($shippingAddress->getCountryId()).'<br/>';
                $shipping_address .= __('T:') . $shippingAddress->getTelephone();
            }
            
            $payment_method = null;
            $payment = $order->getPayment();
            if($payment){
                $dataPayment = $payment->getData();
                $payment_method = isset($dataPayment['additional_information']['method_title']) ? $dataPayment['additional_information']['method_title'] : '';
            }
            
            $items_ordered = array();
            $items = $order->getAllVisibleItems();
            $totalPaid = 0; $TotalSellerAmount = 0;
            if(count($items)){
                $vai_ship_sp_ao = 0; 
                $vai_ship_sp_thuong = 0;
                $vai_ship_sp_thuong_shipped = 0;
                $vai_invoi = false;
                $vai_canceled = false;
                foreach($items as $item){
                    $_product = $item->getProduct();
                    $productOption = $item->getProductOptions();
                    $infoBuyRequest = $productOption['info_buyRequest'];
                    $saleList = $this->getSalelist($order->getId(),$item->getProductId(),@$infoBuyRequest['assignproduct_id']);
                    if($saleList && $saleList->getId()){
                        if($item->getQtyInvoiced() == 0){
                            $vai_invoi = true;
                        }
                        if($item->getQtyCanceled() == 0){
                            $vai_canceled = true;
                        }
                        if(($item->getProductType() == 'downloadable') || ($item->getProductType() == 'virtual')){
                            $vai_ship_sp_ao ++;
                        }else{
                            if($item->getQtyShipped() == 0){
                                $vai_ship_sp_thuong ++;
                            }else{
                                $vai_ship_sp_thuong_shipped ++;
                            }
                        }
                        $subTotalPrice = $item->getBasePrice() * $item->getQtyOrdered();
                        $finalRowPrice = $subTotalPrice;
                        $totalPaid += $finalRowPrice;
                        $TotalSellerAmount += $this->getActualparterprocost($order->getId(),$item->getProductId(),$item->getBasePrice());
                        
                        $items_ordered[] = array(
                            'item_name' => $item->getName(),
                            'item_sku' => $item->getSku(),
                            'item_status' => $item->getStatus(),
                            'item_price_original' => $this->getMkPriceHelper()->currency($item->getOriginalPrice(),true,false),
                            'item_price' => $this->getMkPriceHelper()->currency($item->getBasePrice(),true,false),
                            'item_ordered' => round($item->getQtyOrdered()),
                            'item_shipped' => round($item->getQtyShipped()),
                            'item_invoiced' => round($item->getQtyInvoiced()),
                            'item_canceled' => round($item->getQtyCanceled()),
                            'item_price_subtotal' => $this->getMkPriceHelper()->currency($subTotalPrice,true,false),
                            'item_tax_amount' => $this->getMkPriceHelper()->currency(0,true,false),
                            'item_tax_percent' => '0%',
                            'item_discount_amount' => $this->getMkPriceHelper()->currency(0,true,false),
                            'item_admin_commission' => $this->getMkPriceHelper()->currency($this->getTotalcommision($order->getId(),$item->getProductId(),$item->getBasePrice()),true,false),
                            'item_seller_total' => $this->getMkPriceHelper()->currency($this->getActualparterprocost($order->getId(),$item->getProductId(),$item->getBasePrice()),true,false),
                            'item_row_total' => $this->getMkPriceHelper()->currency($finalRowPrice,true,false)
                        );
                    }
                }
            }
            
            $sellerDiscount = 0;
            if($moduleManager->isEnabled('Magetop_SellerCoupon')){
                $sellerCoupon = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\SellerCoupon\Model\SellerCoupon')
                                                                                   ->getCollection()
                                                                                   ->addFieldToFilter('seller_id',\Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Customer\Model\Session')->getId())
                                                                                   ->addFieldToFilter('order_id',$order->getId())
                                                                                   ->getFirstItem();
                $sellerDiscount = '-'.$block->getMkPriceHelper()->currency($sellerCoupon->getSellerCouponPrice(),true,false);
            }
            
            if($moduleManager->isEnabled('Magetop_SellerCoupon')){
                $_totalSellerAmount = $this->getMkPriceHelper()->currency(($TotalSellerAmount-$sellerCoupon->getSellerCouponPrice()),true,false);
            }else{
                $_totalSellerAmount = $this->getMkPriceHelper()->currency($TotalSellerAmount,true,false);
            }
            
            $data = array(
                'attribute_id' => $order->getId(),
                'order_id' => __('#').$order->getIncrementId(),
                'purchased_date' => $order->getCreatedAt(),
                'status' => $order->getStatusLabel(),
                'purchased_from' => __('Main Website<br/>Main Website Store<br/>Default Store View'),
                'placed_from_ip' => $order->getRemoteIp(),
                'customer_name' => $order->getCustomerName(),
                'email' => $order->getCustomerEmail(),
                'customer_group' => $customer_group,
                'billing_address' => $billing_address,
                'shipping_address' => $shipping_address,
                'payment_method' => $payment_method,
                'shipping_method' => $order->getShippingDescription(),
                'items_ordered' => $items_ordered,
                'subtotal' => $this->getMkPriceHelper()->currency($totalPaid,true,false),
                'shipping_handling' => $this->getMkPriceHelper()->currency(0,true,false),
                'total_orderd_amount' => $this->getMkPriceHelper()->currency($totalPaid,true,false),
                'total_paid' => $this->getMkPriceHelper()->currency(0,true,false),
                'total_refunded' => $this->getMkPriceHelper()->currency(0,true,false),
                'total_due' => $this->getMkPriceHelper()->currency(0,true,false),
                'total_admin_commission' => $this->getMkPriceHelper()->currency($totalPaid-$TotalSellerAmount,true,false),
                'seller_discount' => $sellerDiscount,
                'total_seller_amount' => $_totalSellerAmount
            );
        }catch(\Exception $e){
            $status = false;
            $message = $e->getMessage();
        }
        $responseData = $this->getResponseData($status, $message, $data);
        
        return $this->returnResultJson($responseData);
    }
	/* 
	* get Price Helper
	* return \Magento\Framework\Pricing\Helper\Data
	*/
	function getMkPriceHelper()
	{
		return $this->_priceHelper;
	}
	/* 
	* get data from saleslist model
	*/
	function getSalelist($orderId,$porductId)
	{
		$saleItem = null;
		//$customerSession = $this->_customerSession;
		//if($customerSession->isLoggedIn())
		//{
			//$sellerid = $customerSession->getId();
            $sellerid = $this->getRequest()->getParam("customer_id");
			if($sellerid > 0)
			{
				$saleslistModel = $this->_saleslistFactory->create();
				$collection = $saleslistModel->getCollection()
					->addFieldToFilter('orderid',$orderId)
					->addFieldToFilter('prodid',$porductId)
					->addFieldToFilter('sellerid',$sellerid);
				if(count($collection))
				{
					$saleItem = $collection->getFirstItem();
				}
			}
		//}
		return $saleItem;
	}
	/*
	* get Current Customer Id
	*/
	function getMkCurrentCustomerId()
	{
		/*$customerSession = $this->_customerSession;
		$sellerid = $customerSession->getId();*/
        $sellerid = $this->getRequest()->getParam("customer_id");
		return $sellerid;
	}
    /* 
	* get List Sellers orders
	* @param int $cutomerId
	* return $items
	*/
	function getSellerOrders()
	{
		/*$customerSession = $this->_customerSession;
		$sellerid = $customerSession->getId();*/
        $sellerid = $this->getRequest()->getParam("customer_id");
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
					$orders->addFieldToFilter(array('entity_id','increment_id'),
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
	/**
	* get detail order
	* return $item
	**/
	function getMkDetailOrder()
	{
		$orderId = $this->getRequest()->getParam('order_id',0);
		$order = null;
		/*$customerSession = $this->_customerSession;
		$customerId = $customerSession->getId();*/
        $customerId = $this->getRequest()->getParam("customer_id");
		//if($customerSession->isLoggedIn())
		//{
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
		//}
		return $order;
	}
    /**
	* get total commision fix 5/9/2106 for product custom option by kien magetop.com
	* return $item
	**/
    public function getTotalcommision($order_id,$product_id,$product_price){
        $saleItem = null;
		//$customerSession = $this->_customerSession;
		//if($customerSession->isLoggedIn())
		//{
			//$sellerid = $customerSession->getId();
            $sellerid = $this->getRequest()->getParam("customer_id");
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
		//}
		return $saleItem;
    }
    /**
	* get actual parter procost fix 9/2/2020 for product custom option by kien magetop.com
	* return $item
	**/
    public function getActualparterprocost($order_id,$product_id,$product_price){
        $saleItem = null;
		//$customerSession = $this->_customerSession;
		//if($customerSession->isLoggedIn())
		//{
			//$sellerid = $customerSession->getId();
            $sellerid = $this->getRequest()->getParam("customer_id");
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
		//}
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
}