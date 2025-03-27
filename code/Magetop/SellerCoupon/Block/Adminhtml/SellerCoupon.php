<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Coupon
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerCoupon\Block\Adminhtml;

class SellerCoupon extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_sellerCoupon';
        $this->_blockGroup = 'Magetop_SellerCoupon';
        $this->_headerText = __('Seller Coupon');
        $this->_addButtonLabel = __('Add Coupon');
        parent::_construct();
    }
}