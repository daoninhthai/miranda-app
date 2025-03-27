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
class ProductsGridProductStatus extends AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
        if ($row->getStatus() == 0) {
            $cell = '<span class="status_yellow">Pending</span>';
        }elseif ($row->getStatus() == 1) {
            $cell = '<span class="status_green">Approved</span>';
        }elseif ($row->getStatus() == 2) {
            $cell = '<span class="status_gray">Unapproved</span>';
        }elseif ($row->getStatus() == 3) {
            $cell = '<span class="status_blue">Active</span>';
        }elseif ($row->getStatus() == 4) {
            $cell = '<span class="status_lightpink">Inactive</span>';
        }else{
            $cell = '<span class="status_black"><span>Not Submitted</span>';
        }
        return $cell;
    }
}
