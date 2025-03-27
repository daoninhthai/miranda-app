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
class SellerGridSellerOrders extends AbstractRenderer
{
    protected $_objectmanager;
    
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        $this->_objectmanager = $objectmanager;
    }
    
    public function render(\Magento\Framework\DataObject $row)
    {
        $url = $this->_objectmanager->create('Magento\Backend\Helper\Data')->getUrl('marketplace/orders/index', array('sellerid'=>$row->getUserId()));
        $cell = '<a title="View Payout & Orders" href="'.$url.'">View</a>';
        return $cell;
    }
}