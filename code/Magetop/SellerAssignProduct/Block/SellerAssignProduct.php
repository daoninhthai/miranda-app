<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Assign_Product
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerAssignProduct\Block;

class SellerAssignProduct extends \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;    
    protected $_sellerassignproductFactory; 
    protected $_customerSession;
    protected $_customerFactory;  
    protected $_resource; 
    protected $_reviewsFactory;
            
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,    
        \Magento\Framework\View\Element\Template\Context $context,
        \Magetop\SellerAssignProduct\Model\SellerAssignProductFactory $sellerassignproductFactory,              
		\Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory, 
        \Magento\Framework\App\ResourceConnection $resource,
        \Magetop\Marketplace\Model\ReviewsFactory $reviewsFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;                
        $this->_sellerassignproductFactory = $sellerassignproductFactory;             
		$this->_customerSession = $customerSession;
        $this->_customerFactory = $customerFactory;  
		$this->_resource = $resource;
        $this->_reviewsFactory = $reviewsFactory;
        parent::__construct($context, $data);              
    }
    
    public function getDataCollection()
    {
        //get collection of data 
        if($_product = $this->_coreRegistry->registry('product')){
			$productId = $_product->getId();
		}        
        $collection = $this->_sellerassignproductFactory->create()->getCollection();
        $collection->setOrder('id', 'ASC');   
        $collection->addFieldToFilter('status', 1);   
        $collection->addFieldToFilter('product_id', $productId);   
        $tableMKuser = $this->_resource->getTableName('multivendor_user');
        $collection->getSelect()->joinLeft(array('mk_user'=>$tableMKuser),'main_table.seller_id = mk_user.user_id',array())
                                ->where('mk_user.userstatus = 1');
        $collection->getSelect()->group('main_table.seller_id');
        return $collection;   
            
    }    
    
    public function getCountNewProduct(){
        //get collection of data 
        if($_product = $this->_coreRegistry->registry('product')){
			$productId = $_product->getId();
		}        
        $collection = $this->_sellerassignproductFactory->create()->getCollection();
        $collection->setOrder('id', 'ASC');   
        $collection->addFieldToFilter('status', 1);   
        $collection->addFieldToFilter('product_id', $productId);  
        $collection->addFieldToFilter('product_condition', 'new');   
        $tableMKuser = $this->_resource->getTableName('multivendor_user');
        $collection->getSelect()->joinLeft(array('mk_user'=>$tableMKuser),'main_table.seller_id = mk_user.user_id',array())
                                ->where('mk_user.userstatus = 1');
        $collection->getSelect()->group('main_table.seller_id');
        return $collection;   
    } 
    
    public function getCountUsedProduct(){
        //get collection of data 
        if($_product = $this->_coreRegistry->registry('product')){
			$productId = $_product->getId();
		}        
        $collection = $this->_sellerassignproductFactory->create()->getCollection();
        $collection->setOrder('id', 'ASC');   
        $collection->addFieldToFilter('status', 1);   
        $collection->addFieldToFilter('product_id', $productId);  
        $collection->addFieldToFilter('product_condition', 'used');   
        $tableMKuser = $this->_resource->getTableName('multivendor_user');
        $collection->getSelect()->joinLeft(array('mk_user'=>$tableMKuser),'main_table.seller_id = mk_user.user_id',array())
                                ->where('mk_user.userstatus = 1');
        $collection->getSelect()->group('main_table.seller_id');
        return $collection; 
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
    
    /**
	* get seller reviews
	**/
	function getMKsellerReview($userId)
	{
		$reviewsModel = $this->_reviewsFactory->create();
		return $reviewsModel->getMKReview($userId);
	}
        
    function getMkBaseMediaUrl()
	{
		return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
	}   
    
    public function getLoadProduct($id)
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Catalog\Model\Product')->load($id);
    } 
    
    public function getUrlImage($url){
		$srcImage = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $url;
		return $srcImage;
	} 
    
    public function getUrlAddToCart($productId){
        $_product = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Catalog\Model\Product')->load($productId);
        return \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Catalog\Block\Product\View')->getSubmitUrl($_product);
	} 
}
?>