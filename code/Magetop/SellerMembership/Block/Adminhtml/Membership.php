<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Membership
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerMembership\Block\Adminhtml;

class Membership extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_membership';
        $this->_blockGroup = 'Magetop_SellerMembership';
        $this->_headerText = __('Membership');
        $this->_addButtonLabel = __('Add Membership');
        parent::_construct();
    }
}