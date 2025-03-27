<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Product_Attribute_Management
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/

namespace Magetop\ProductAttributeManagement\Block\Adminhtml\Grid\Column;
use \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
class ProductAttributeManagementGridStatus extends AbstractRenderer
{
    protected $_productAttributeCollectionFactory;
    protected $_objectmanager;
    
    public function __construct(
        \Magetop\ProductAttributeManagement\Model\ProductAttributeManagementFactory $productAttributeFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {	
        $this->_productAttributeCollectionFactory = $productAttributeFactory;	
        $this->_objectmanager = $objectmanager;
    }
    
    public function render(\Magento\Framework\DataObject $row)
    {
        $productAttributeModel = $this->_productAttributeCollectionFactory->create()->getCollection()->addFieldToFilter('attribute_name',$row->getAttributeId())->getFirstItem();
        if($productAttributeModel->getData()){
            if($productAttributeModel['status']){
                $cell = 'YES';
            }else{
                $cell = 'NO';
            }
        }else{
            $cell = '';
        }
        return $cell;
    }
}