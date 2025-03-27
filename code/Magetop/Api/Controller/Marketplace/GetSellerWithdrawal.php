<?php
/**
 * @author      Magetop
 * @package     Magetop_Api
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Api\Controller\Marketplace;

use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Magetop\Api\Helper\Data as DataHelper;

class GetSellerWithdrawal extends \Magetop\Api\Controller\AbstractController
{
    protected $_resource;
	protected $_transactionsFactory;    
    protected $_partnerFactory;   
    protected $_objectmanager;
    protected $_customerSession;
    protected $_priceHelper;
    protected $_saleslistFactory;
    protected $_customerFactory;
    
    public function __construct(
        ResourceConnection $resource,
		CustomerFactory $customerFactory,
        \Magetop\Marketplace\Model\TransactionsFactory $transactionsFactory,  
        \Magetop\Marketplace\Model\PartnerFactory $partnerFactory,      
        \Magento\Framework\ObjectManagerInterface $objectmanager,  
        \Magento\Customer\Model\Session $customerSession,  
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Magetop\Marketplace\Model\SaleslistFactory $saleslistFactory,
        Context $context,
        EventManager $eventManager,
        AppEmulation $appEmulation,
        DataHelper $dataHelper
    ) {
        $this->_resource = $resource;
		$this->_customerFactory = $customerFactory;
        $this->_transactionsFactory = $transactionsFactory;
        $this->_partnerFactory = $partnerFactory;   
        $this->_objectmanager = $objectmanager;   
        $this->_customerSession = $customerSession;   
        $this->_priceHelper = $priceHelper;
		$this->_saleslistFactory = $saleslistFactory;
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
            $_transactionCollection = $this->getTransactions();
            $_transactionDetail = $this->getDetailTransaction();
            $paymentmethodCollection = $this->getPaymentMethods();
            
            $balance =  $this->getPrice($_transactionDetail['amountremain']-$this->getPendingAmount(\Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Customer\Model\Session')->getId()));
            $paymentData = array();
            if($paymentmethodCollection->count()){
                foreach($paymentmethodCollection as $payment){
                    $paymentData[] = array(
                        'name' => $payment->getName(),
                        'description' => $payment->getDescription(),
                        'fee' => $payment->getFee()
                    );
                }
            }
            $totalAmountReceived = $this->getPrice($_transactionDetail['amountreceived']);
            $totalAmountPending = $this->getPrice($this->getPendingAmount(\Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Customer\Model\Session')->getId()));
            $transactionsData = array();
            if(count($_transactionCollection)){
                foreach($_transactionCollection as $transaction){
                    if($transaction->getTransactionIdOnline()){
                        $payment_method = __('Marketplace Paypal Adaptive');
                    }else{
                        $paymentDetail = $this->getPaymentMethodById($transaction['payment_id']);
                        $payment_method = $paymentDetail['name'];
                    }
                    if ($transaction->getPaidStatus() == 1) $status = __('Pending');
                    if ($transaction->getPaidStatus() == 2) $status = __('Completed');
                    if ($transaction->getPaidStatus() == 3) $status = __('Canceled');
                    $transactionsData[] = array(
                        'id' => $transaction->getId(),
                        'transaction_id' => $transaction->getTransactionIdOnline()?$transaction->getTransactionIdOnline():$transaction->getTransactionId(),
                        'payment_method' => $payment_method,
                        'amount' => $this->getPrice($transaction->getTransactionAmount()),
                        'admin_comment' => $transaction->getAdminComment(),
                        'created' => $transaction->getCreatedAt(),
                        'status' => $status
                    );
                }
            }
            $data = array(
                'balance' => $balance,
                'payment' => $paymentData,
                'total_amount_received' => $totalAmountReceived,
                'total_amount_pending' => $totalAmountPending,
                'transactions_data' => $transactionsData
            );
        }catch(\Exception $e){
            $status = false;
            $message = $e->getMessage();
        }
        $responseData = $this->getResponseData($status, $message, $data);
        
        return $this->returnResultJson($responseData);
    }
    
    public function getPaymentMethods()
    {
        return $this->_objectmanager->create('Magetop\Marketplace\Model\Payments')->getCollection()
                                                                                  ->addFieldToFilter('status',1)   
                                                                                  ->setOrder('sortorder','ASC');
    }
    
    public function getPaymentMethodById($id)
    {
        return $this->_objectmanager->create('Magetop\Marketplace\Model\Payments')->load($id)->getData();
    }
    
    public function getPrice($price)
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Pricing\Helper\Data')->currency($price,true,false);
    }
    
    public function getPendingAmount($seller_id)
    {
        $data = $this->_objectmanager->create('Magetop\Marketplace\Model\Transactions')->getCollection()
                                                                                       ->addFieldToFilter('seller_id',$seller_id)
                                                                                       ->addFieldToFilter('paid_status',1);
        $value = 0;
        foreach($data as $dt){
            $value = $value + $dt['transaction_amount'];
        }
        return $value;
    }
    
    public function checkAmountPay($can_withdraw,$amount)
    {
        if($can_withdraw >= $amount){
            return true;
        }else{
            return false;
        }
    }
    
    function getDetailTransaction()
	{
        $customerSession = $this->_customerSession;
		$sellerid = $customerSession->getId();
        $collection = $this->_partnerFactory->create()->getCollection()->addFieldToFilter('sellerid',$sellerid)->getFirstItem();
        return $collection;
	}
	/**
	* get list Transactions
	* @return $items
	**/
	function getTransactions()
	{
	   	$customerSession = $this->_customerSession;
		$sellerid = $customerSession->getId();
	   	$collection = null;
        if($sellerid > 0)
		{
            $collection = $this->_transactionsFactory->create()->getCollection()->addFieldToFilter('seller_id',$sellerid);
            $params = $this->getRequest()->getPost();
        	if(count($params)){
        		if(isset($params['transaction_id']) && $params['transaction_id'] != ''){
        			$transactionId = trim($params['transaction_id']);
        			$collection->addFieldToFilter('transaction_id',array('like'=>'%'.$transactionId.'%'));	
        		}
        		$fromDate = isset($params['from_date']) ? trim($params['from_date']) : '';
        		$toDate = isset($params['to_date']) ? trim($params['to_date']) : '';
        		if($fromDate != '' && $toDate == ''){
        			$collection->addFieldToFilter('created_at',array('gteq'=>$fromDate));
        		}elseif($fromDate == '' && $toDate != ''){
        			$collection->addFieldToFilter('created_at',array('lteq'=>$toDate));
        		}elseif($fromDate != '' && $toDate != ''){
        			$collection->addFieldToFilter('created_at',array('gteq'=>$fromDate));
        			$collection->addFieldToFilter('created_at',array('lteq'=>$toDate));
        		}
            }
            $collection->setOrder('id','DESC');
            $limit = $this->getRequest()->getParam('limit',5);
			if($limit > 0)
			{
				$collection->setPageSize($limit);
			}
            $curPage = $this->getRequest()->getParam('p',1);
			if($curPage > 1)
			{
				$collection->setCurPage($curPage);
			}
        }
        return $collection;
	}
    
    protected function _prepareLayout()
    {
        $collection = $this->getTransactions();
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
    
    /* 
	* get Price Helper
	* return \Magento\Framework\Pricing\Helper\Data
	*/
	function getMkPriceHelper()
	{
		return $this->_priceHelper;
	}
	/*
	* get Current Customer Id
	*/
	function getMkCurrentCustomerId()
	{
		$customerSession = $this->_customerSession;
		$sellerid = $customerSession->getId();
		return $sellerid;
	}
	/**
	* get detail transaction
	* return $item
	**/
	function getMkDetailTransaction()
	{
		$transactionId = $this->getRequest()->getParam('id',0);
		$transaction = null;
		$customerSession = $this->_customerSession;
		$customerId = $customerSession->getId();
		if($customerSession->isLoggedIn())
		{
			if($transactionId > 0)
			{
				$collection = $this->_transactionsFactory->create()->getCollection()
                                                                    ->addFieldToFilter('id',$transactionId)
                                                                    ->addFieldToFilter('seller_id',$customerId)->getFirstItem();
			}
		}
		return $collection;
	}
    
    function getMkDetailTransactionForPay($transactionId)
	{
		$transaction = null;
		if($transactionId > 0)
		{
			$collection = $this->_transactionsFactory->create()->getCollection()
                                                                ->addFieldToFilter('id',$transactionId)
                                                                ->getFirstItem();
		}
		return $collection;
	}
    
    function getDetailPartnerForPay($sellerid)
	{
        $collection = $this->_partnerFactory->create()->getCollection()->addFieldToFilter('sellerid',$sellerid)->getFirstItem();
        return $collection;
	}
    
    public function getSellerDetailById($seller_id){
		$customer = $this->_objectmanager->create('Magento\Customer\Model\Customer')->load( $seller_id );
		$customer_name = $customer->getData('firstname') . ' ' . $customer->getData('lastname');
        return $customer_name;
    }
    /* 
	* get data from saleslist model
	*/
	function getSalelist($transactionId)
	{
		$collection = null;
		$customerSession = $this->_customerSession;
		if($customerSession->isLoggedIn())
		{
			$sellerid = $customerSession->getId();
			if($sellerid > 0)
			{
				$saleslistModel = $this->_saleslistFactory->create();
				$collection = $saleslistModel->getCollection()
                        					 ->addFieldToFilter('transid',$transactionId)
                        					 ->addFieldToFilter('sellerid',$sellerid);
			}
		}
		return $collection;
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
	* get Mk config
	* @return string 
	**/
	function getMkConfig($field)
	{
		return $this->_scopeConfig->getValue($field, ScopeInterface::SCOPE_STORE);
	}
	function getMkBaseMediaUrl()
	{
		return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
	}
}