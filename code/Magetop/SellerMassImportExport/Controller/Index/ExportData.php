<?php
/**
 * Copyright © 2020 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magetop\SellerMassImportExport\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\ImportExport\Model\Export as ExportModel;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;

class ExportData extends \Magento\Framework\App\Action\Action
{
    const ADMIN_RESOURCE = 'Magento_ImportExport::export';
    /**
     * @var \Magento\Framework\App\Response\Http\FileFactory
     */
    protected $fileFactory;
    protected $_sessioncustomer;
    protected $_product;
    protected $_resource;
        
    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Response\Http\FileFactory $fileFactory
     */
    public function __construct(
        Context $context,
        FileFactory $fileFactory,
        \Magento\Customer\Model\Session $sessioncustomer,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->fileFactory = $fileFactory;
        $this->_sessioncustomer = $sessioncustomer;   
        $this->_product = $product;     
        $this->_resource = $resource; 
        parent::__construct($context);
    }

    /**
     * Load data with filter applying and create file for download.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        if ($this->getRequest()->getPost(ExportModel::FILTER_ELEMENT_GROUP)) {
            try {
                /** @var $model \Magento\ImportExport\Model\Export */
                $model = $this->_objectManager->create('Magento\ImportExport\Model\Export');
                $model->setData($this->getRequest()->getParams());
                //get all products of currently seller
                /*$tableMKproduct = $this->_resource->getTableName('multivendor_product');
    			$collection = $this->_product->getCollection();
    			$collection->addAttributeToSelect(array('*'));
                $collection->getSelect()->joinLeft(array('mk_product'=>$tableMKproduct),'e.entity_id = mk_product.product_id',array())
					       ->where('mk_product.user_id=?',$this->_sessioncustomer->getCustomer()->getId());
                           
                $export_text = 'sku,store_view_code,attribute_set_code,product_type,categories,product_websites,name,description,short_description,weight,product_online,tax_class_name,visibility,price,special_price,special_price_from_date,special_price_to_date,url_key,meta_title,meta_keywords,meta_description,base_image,base_image_label,small_image,small_image_label,thumbnail_image,thumbnail_image_label,swatch_image,swatch_image_label,created_at,updated_at,new_from_date,new_to_date,display_product_options_in,map_price,msrp_price,map_enabled,gift_message_available,custom_design,custom_design_from,custom_design_to,custom_layout_update,page_layout,product_options_container,msrp_display_actual_price_type,country_of_manufacture,additional_attributes,qty,out_of_stock_qty,use_config_min_qty,is_qty_decimal,allow_backorders,use_config_backorders,min_cart_qty,use_config_min_sale_qty,max_cart_qty,use_config_max_sale_qty,is_in_stock,notify_on_stock_below,use_config_notify_stock_qty,manage_stock,use_config_manage_stock,use_config_qty_increments,qty_increments,use_config_enable_qty_inc,enable_qty_increments,is_decimal_divided,website_id,related_skus,related_position,crosssell_skus,crosssell_position,upsell_skus,upsell_position,additional_images,additional_image_labels,hide_from_product_page,custom_options,bundle_price_type,bundle_sku_type,bundle_price_view,bundle_weight_type,bundle_values,bundle_shipment_type,associated_skus
';
                foreach($collection as $pr){
                    $export_text .= $pr->getData('sku').',';
                    $export_text .= $pr->getData('store_view_code').',';
                    $attribute_set_code = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Eav\Api\AttributeSetRepositoryInterface')->get($pr->getData('attribute_set_id'))->getAttributeSetName();
                    $export_text .= $attribute_set_code.',';
                    $export_text .= $pr->getData('type_id').',';
                    $export_text .= '"';                    
                    $cats = $pr->getCategoryIds();$i = 1;
                    if(count($cats) ){
                        foreach($cats as $cat){
                            $_category = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Catalog\Model\Category')->load($cat);
                            $i < count($cats)?$export_text .= $_category->getName().',':$export_text .= $_category->getName();
                            $i ++;
                        }
                    }
                    $export_text .= '"'.',';   
                    $export_text .= 'base'.','; 
                    $export_text .= '"'.$pr->getData('name').'"'.',';  
                    $export_text .= '"'.htmlspecialchars($pr->getData('description')).'"'.',';       
                    $export_text .= '"'.htmlspecialchars($pr->getData('short_description')).'"'.',';  
                    $export_text .= $pr->getData('weight').',';
                    $export_text .= $pr->getData('status').',';   
                    $tax_class_name = array(0=>'None',2=>'Taxable Goods');
                    $export_text .= '"'.$tax_class_name[$pr->getData('tax_class_id')].'"'.',';  
                    $visibility = array(1=>'Not Visible Individually',2=>'Catalog',3=>'Search',4=>'Catalog, Search');
                    $export_text .= '"'.$visibility[$pr->getData('visibility')].'"'.',';  
                    $export_text .= $pr->getData('price').',';
                    $export_text .= $pr->getData('special_price').',';
                    $export_text .= '"'.date('n/j/y', strtotime($pr->getData('special_from_date'))).'"'.',';  
                    $export_text .= '"'.date('n/j/y', strtotime($pr->getData('special_to_date'))).'"'.','; 
                    $export_text .= $pr->getData('url_key').',';
                    $export_text .= '"'.$pr->getData('meta_title').'"'.',';
                    $export_text .= '"'.$pr->getData('meta_keyword').'"'.',';
                    $export_text .= '"'.$pr->getData('meta_description').'"'.',';
                    $export_text .= $pr->getData('image').',';
                    $export_text .= ',';
                    $export_text .= $pr->getData('small_image').',';
                    $export_text .= ',';
                    $export_text .= $pr->getData('thumbnail').',';
                    $export_text .= ',';
                    $export_text .= $pr->getData('swatch_image').',';
                    $export_text .= ',';
                    $export_text .= '"'.date('n/j/y, g:i A', strtotime($pr->getData('created_at'))).'"'.','; 
                    $export_text .= '"'.date('n/j/y, g:i A', strtotime($pr->getData('updated_at'))).'"'.','; 
                    $export_text .= '"'.date('n/j/y', strtotime($pr->getData('news_from_date'))).'"'.','; 
                    $export_text .= '"'.date('n/j/y', strtotime($pr->getData('news_to_date'))).'"'.','; 
                    $display_product_options_in = array('container1'=>'Product Info Column','container2'=>'Block after Info Column');
                    $export_text .= '"'.$display_product_options_in[$pr->getData('options_container')].'"'.',';  
                    $export_text .= ',';
                    $export_text .= ',';
                    $export_text .= ',';
                    $export_text .= '"Use config"'.',';  
                    $export_text .= \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Framework\View\Design\Theme\ThemeProviderInterface')->getThemeById($pr->getData('custom_design'))->getData('theme_title').',';;
                    $export_text .= '"'.date('n/j/y', strtotime($pr->getData('custom_design_from'))).'"'.','; 
                    $export_text .= '"'.date('n/j/y', strtotime($pr->getData('custom_design_to'))).'"'.','; 
                    $export_text .= $pr->getData('custom_layout_update').','; 
                    $page_layout = array(''=>'No layout updates','empty'=>'Empty','1column'=>'1 column','2columns-left'=>'2 columns with left bar','2columns-right'=>'2 columns with right bar','3columns'=>'3 columns'); 
                    $export_text .= $page_layout[$pr->getData('page_layout')].',';  
                    $export_text .= ',';
                    $export_text .= '"Use config"'.','; 
                    $countries = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Directory\Model\Config\Source\Country')->toOptionArray(); //Load an array of countries
                    foreach($countries as $country){
                        if($country['value'] == $pr->getData('country_of_manufacture')){
                            $country_of_manufacture = $country['label'];
                        }
                    }
                    if($pr->getData('country_of_manufacture') == '')$country_of_manufacture = '';
                    $export_text .= $country_of_manufacture.',';
                    $additional_attributes = array(''=>'No layout updates','empty'=>'Empty','1column'=>'1 column','2columns-left'=>'2 columns with left bar','2columns-right'=>'2 columns with right bar','3columns'=>'3 columns');
                    $export_text .= '"custom_layout="'.$additional_attributes[$pr->getData('custom_layout')].'""'.',';  
                    $productStockObj = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItem($pr->getData('entity_id'), $pr->getStore()->getWebsiteId());
                    $export_text .= $productStockObj->getData('qty').',';    
                    $export_text .= $productStockObj->getData('min_qty').',';  
                    $export_text .= $productStockObj->getData('use_config_min_qty').',';      
                    $export_text .= $productStockObj->getData('is_qty_decimal').',';   
                    $export_text .= $productStockObj->getData('backorders').',';
                    $export_text .= $productStockObj->getData('use_config_backorders').',';   
                    $export_text .= $productStockObj->getData('min_sale_qty').',';   
                    $export_text .= $productStockObj->getData('use_config_min_sale_qty').',';  
                    $export_text .= $productStockObj->getData('max_sale_qty').',';    
                    $export_text .= $productStockObj->getData('use_config_max_sale_qty').',';   
                    $export_text .= $productStockObj->getData('is_in_stock').',';   
                    $export_text .= $productStockObj->getData('notify_stock_qty').',';  
                    $export_text .= $productStockObj->getData('use_config_notify_stock_qty').','; 
                    $export_text .= $productStockObj->getData('manage_stock').',';  
                    $export_text .= $productStockObj->getData('use_config_manage_stock').',';  
                    $export_text .= $productStockObj->getData('use_config_qty_increments').',';
                    $export_text .= $productStockObj->getData('qty_increments').',';
                    $export_text .= $productStockObj->getData('use_config_enable_qty_inc').',';
                    $export_text .= $productStockObj->getData('enable_qty_increments').',';
                    $export_text .= $productStockObj->getData('is_decimal_divided').',';
                    $export_text .= $productStockObj->getData('website_id').',';
                    $export_text .= ',';
                    $export_text .= ',';
                    $export_text .= ',';
                    $export_text .= ',';
                    $export_text .= ',';
                    $export_text .= ',';
                    $export_text .= ',';
                    $export_text .= ',';
                    $export_text .= ',';
                    $export_text .= ',';
                    $export_text .= ',';
                    $export_text .= ',';
                    $export_text .= ',';
                    $export_text .= ',';
                    $export_text .= ',';
                    $export_text .= ',';
                    $export_text .= '
';
                }*/
                return $this->fileFactory->create(
                    $model->getFileName(),
                    $model->export(),
                    DirectoryList::VAR_DIR,
                    $model->getContentType()
                );
            } catch (LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->messageManager->addError(__('Please correct the data sent value.'));
            }
        } else {
            $this->messageManager->addError(__('Please correct the data sent value.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('*/*/export');
        return $resultRedirect;
    }
}
