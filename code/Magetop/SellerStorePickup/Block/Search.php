<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Store_Pickup
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerStorePickup\Block;

class Search extends \Magento\Framework\View\Element\Template
{
    protected $_product;
    protected $_sellerstorepickupFactory; 
    protected $_customerSession;
    protected $_customerFactory;  
    protected $_resource; 
            
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Product $product, 
        \Magetop\SellerStorePickup\Model\SellerStorePickupFactory $sellerstorepickupFactory,              
		\Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory, 
        \Magento\Framework\App\ResourceConnection $resource,
        array $data = []
    ) {
        $this->_product = $product;  
        $this->_sellerstorepickupFactory = $sellerstorepickupFactory;             
		$this->_customerSession = $customerSession;
        $this->_customerFactory = $customerFactory;  
		$this->_resource = $resource;
        parent::__construct($context, $data); 
        //get collection of data 
        $collection = $this->_sellerstorepickupFactory->create()->getCollection();
        $collection->setOrder('id', 'ASC');   
        $collection->addFieldToFilter('status', 1);
        
        if($this->getType() == 'distance'){
            $data_post = $this->getRequest()->getParam('store_ids');
            $store_ids = explode('store_id',$data_post??'');
            $collection->addFieldToFilter('id', array('in' => $store_ids));
        }else{
            $shop_location = $this->getRequest()->getParam('shop_location');
    		if ($shop_location != "") {
                $shop_location = trim($shop_location);
                $collection->addFieldToFilter('shop_location', array('like' => '%' . $shop_location . '%'));
            }
            
            $store_name = $this->getRequest()->getParam('store_name');
    		if ($store_name != "") {
                $store_name = trim($store_name);
                $collection->addFieldToFilter('title', array('like' => '%' . $store_name . '%'));
            }  
            
            $phone_number = $this->getRequest()->getParam('phone_number');
    		if ($phone_number != "") {
                $phone_number = trim($phone_number);
                $collection->addFieldToFilter('phone_number', array('like' => '%' . $phone_number . '%'));
            } 
            
            $address = $this->getRequest()->getParam('address');
    		if ($address != "") {
                $address = trim($address);
                $collection->addFieldToFilter('address', array('like' => '%' . $address . '%'));
            } 
            
            $country = $this->getRequest()->getParam('country');
    		if ($country != "") {
                $country = trim($country);
                $collection->addFieldToFilter('country', array('like' => '%' . $country . '%'));
            } 
            
            $state = $this->getRequest()->getParam('state');
    		if ($state != "") {
                $state = trim($state);
                $collection->addFieldToFilter('state', array('like' => '%' . $state . '%'));
            } 
            
            $city = $this->getRequest()->getParam('city');
    		if ($city != "") {
                $city = trim($city);
                $collection->addFieldToFilter('city', array('like' => '%' . $city . '%'));
            } 
            
            $zipcode = $this->getRequest()->getParam('zipcode');
    		if ($zipcode != "") {
                $zipcode = trim($zipcode);
                $collection->addFieldToFilter('zipcode', array('like' => '%' . $zipcode . '%'));
            } 
            
            $product_name = $this->getRequest()->getParam('product_name');
    		if ($product_name != "") {
                $product_name = trim($product_name);
                $tableMKproduct = $this->_resource->getTableName('multivendor_product');
        		$collection1 = $this->_product->getCollection();
                $collection1->addAttributeToFilter('name', array('like' => '%'.$product_name.'%'));
        		$collection1->addAttributeToSelect(array('*'));
        		$collection1->addAttributeToFilter('status',1);
        		$collection1->getSelect()->joinLeft(array('mk_product'=>$tableMKproduct),'e.entity_id = mk_product.product_id',array())
        				->where('mk_product.user_id IS NOT NULL')
                        ->where('mk_product.status = 1');
                //Kien 19/5/2020 - update filter seller approve        
                $tableMKuser = $this->_resource->getTableName('multivendor_user');
                $collection1->getSelect()->joinLeft(array('mk_user'=>$tableMKuser),'mk_product.user_id = mk_user.user_id',array('sellerid'=>"mk_product.user_id"))
                        ->where('mk_user.userstatus = 1');
                $seller_id = array();
                foreach($collection1 as $data){
                    $seller_id[$data->getSellerid()] = $data->getSellerid();
                }
                $collection->addFieldToFilter('seller_id', array('in' => $seller_id));
            }
        }
        $this->setCollection($collection);
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
?>