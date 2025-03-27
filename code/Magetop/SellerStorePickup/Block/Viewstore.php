<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Store_Pickup
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerStorePickup\Block;

class Viewstore extends \Magento\Framework\View\Element\Template
{
    protected $_gridFactory; 
    protected $_coreRegistry; 
    protected $_customerSession;
    protected $_customerFactory; 
    protected $_resource; 
     
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magetop\SellerStorePickup\Model\SellerStorePickupFactory $gridFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory, 
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
    ) {
        $this->_gridFactory = $gridFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession = $customerSession;
        $this->_customerFactory = $customerFactory;  
        $this->_resource = $resource;
        parent::__construct($context, $data);
        
        //get collection of data 
        $data = $this->_gridFactory->create()->getCollection()->addFieldToFilter('id',$this->getRequest()->getParam('id'))->getFirstItem();      
        $this->setCollection($data);
    }
    
    public function getSellerInfoById($seller_id)
    {
        $tableSellers = $this->_resource->getTableName('multivendor_user');
		$customerModel = $this->_customerFactory->create();
		$sellers = $customerModel->getCollection();
		$sellers->getSelect()->joinLeft(array('table_sellers'=>$tableSellers),'e.entity_id = table_sellers.user_id',array('*'))->where('table_sellers.userstatus = 1');
        $sellers->getSelect()->where('table_sellers.user_id=?',$seller_id);
        return $sellers->getData();        
    }
        
    function getMkBaseMediaUrl()
	{
		return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
	} 
}