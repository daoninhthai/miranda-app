<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Coupon
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerCoupon\Block;
class Addnew extends \Magento\Framework\View\Element\Template
{
    protected $_gridFactory; 
    protected $_coreRegistry; 
     
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magetop\SellerCoupon\Model\SellerCouponFactory $gridFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_gridFactory = $gridFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
        $this->pageConfig->getTitle()->set(__('Add new Coupon'));
    }
}