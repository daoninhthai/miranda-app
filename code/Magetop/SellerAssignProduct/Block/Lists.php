<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Assign_Product
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerAssignProduct\Block;

class Lists extends \Magento\Framework\View\Element\Template
{
    protected $_productFactory; 
    protected $_sellerassignproductFactory; 
    protected $_customerSession;
    protected $_customerFactory;  
    protected $_resource; 
    //protected $_storeManager;
            
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Product $productFactory,
        \Magetop\SellerAssignProduct\Model\SellerAssignProductFactory $sellerassignproductFactory,              
		\Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\CustomerFactory $customerFactory, 
        \Magento\Framework\App\ResourceConnection $resource,
        //\Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_productFactory = $productFactory;
        $this->_sellerassignproductFactory = $sellerassignproductFactory;             
		$this->_customerSession = $customerSession;
        $this->_customerFactory = $customerFactory;  
		$this->_resource = $resource;
        //$this->_storeManager = $storeManager;
        parent::__construct($context, $data); 
        
        //get collection of data 
        $collection = $this->_sellerassignproductFactory->create()->getCollection();
        $collection->addFieldToFilter('seller_id',$this->_customerSession->getId()); 
        $collection->setOrder('id', 'ASC'); 
        $litmit = $this->getRequest()->getParam('limit',5);
        if($litmit > 0){
			$collection->setPageSize($litmit);
		}
		$curPage = $this->getRequest()->getParam('p',1);
		if($curPage > 1)
		{
			$collection->setCurPage($curPage);
		}  
        $this->setCollection($collection);
    }
    
    public function getLoadProduct($id)
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Catalog\Model\Product')->load($id);
    } 
    
    public function getUrlImage($url){
		$srcImage = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $url;
		return $srcImage;
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
     
    protected function _prepareLayout()
    {
        $collection = $this->getCollection();
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
     * @return  method for get pager html
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}
?>