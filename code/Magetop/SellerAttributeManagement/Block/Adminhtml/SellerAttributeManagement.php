<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Attribute_Management
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerAttributeManagement\Block\Adminhtml; 
use Magento\Backend\Block\Widget\Grid\Container;
class SellerAttributeManagement extends Container
{
   /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_sellerAttributeManagement';
        $this->_blockGroup = 'Magetop_SellerAttributeManagement';
        $this->_headerText = __('Manage Seller Attribute');
        $this->_addButtonLabel = __('Add New Attribute');
        parent::_construct();
    }
}