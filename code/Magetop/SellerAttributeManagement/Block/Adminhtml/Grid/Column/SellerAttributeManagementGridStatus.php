<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Attribute_Management
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerAttributeManagement\Block\Adminhtml\Grid\Column;
use \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
class SellerAttributeManagementGridStatus extends AbstractRenderer
{
    
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($row->getIsActive() || $row->getStatus()) {
            $cell = 'Yes';
        } else {
            $cell = 'No';
        }
        return $cell;
    }
}