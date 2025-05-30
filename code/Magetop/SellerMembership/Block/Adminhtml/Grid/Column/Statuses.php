<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Membership
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerMembership\Block\Adminhtml\Grid\Column;
use \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
class Statuses extends AbstractRenderer
{
    
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($row->getIsActive() || $row->getStatus()) {
            $cell = '<span class="grid-severity-notice"><span>Enabled</span></span>';
        } else {
            $cell = '<span class="grid-severity-critical"><span>Disabled</span></span>';
        }
        return $cell;
    }
}