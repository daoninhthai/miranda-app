<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Assign_Product
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerAssignProduct\Block;

class EditProduct extends \Magento\Framework\View\Element\Template
{
    protected $_productFactory; 
    protected $_coreRegistry; 
    protected $_customerSession;
     
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Product $productFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->_productFactory = $productFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
    }
    
    public function getDataAssign($id)
    {
        return \Magento\Framework\App\ObjectManager::getInstance()->create('\Magetop\SellerAssignProduct\Model\SellerAssignProduct')->load($id);
    } 
    
    public function getLoadProduct($id)
    {
        return $this->_productFactory->load($id);
    }  
    
    public function getUrlImage($url){
		$srcImage = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . $url;
		return $srcImage;
	}        
}