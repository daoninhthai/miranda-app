<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Block\Adminhtml\Grid\Column;

use \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magetop\Marketplace\Model\Image as ImageModel;
use Magento\Catalog\Helper\Image as ImageProduct;

class ProductsGridProductImage extends AbstractRenderer
{
    protected $_imageModel;
    protected $_imageProduct;
    protected $_product;
    protected $_objectmanager;

    public function __construct(
		ImageModel $ImageModel,
        ImageProduct $ImageProduct,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
		$this->_imageModel = $ImageModel;
        $this->_imageProduct = $ImageProduct;
        $this->_product = $product;
        $this->_objectmanager = $objectmanager;
    }
    
    public function render(\Magento\Framework\DataObject $row)
    {
        $product = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Catalog\Model\Product')->load($row->getProductId());      
		$image = 'category_page_grid';
		$productImage = $this->_imageProduct->init($product, 'product_listing_thumbnail_preview')->getUrl();
    	$strImage = '<img width="50" height="50" src="'.$productImage.'" />';
    	return $strImage;
    }
    
    function getMediaBaseUrl() {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $om->get('Magento\Store\Model\StoreManagerInterface');
        $currentStore = $storeManager->getStore();
        return $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }
        
    function getBaseUrl() {
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $om->get('Magento\Store\Model\StoreManagerInterface');
        $currentStore = $storeManager->getStore();
        return $currentStore->getBaseUrl();
    }    
}