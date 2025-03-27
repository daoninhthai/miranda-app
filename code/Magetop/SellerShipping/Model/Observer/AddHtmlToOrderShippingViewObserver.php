<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Shipping
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerShipping\Model\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class AddHtmlToOrderShippingViewObserver implements ObserverInterface
{

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     */
    public function __construct(\Magento\Framework\ObjectManagerInterface $objectmanager)
    {
        $this->_objectManager = $objectmanager;
    }

    public function execute(EventObserver $observer)
    {
        if($observer->getElementName() == 'order_shipping_view')
        {
            $orderShippingViewBlock = $observer->getLayout()->getBlock($observer->getElementName());
            $order = $orderShippingViewBlock->getOrder();
            $localeDate = $this->_objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
            $sellerShippingBlock = $this->_objectManager->create('Magento\Framework\View\Element\Template');
            $sellerShippingBlock->setSellerFlatRateShipping($order->getSellerFlatRateShipping());
            $sellerShippingBlock->setSellerTableRateShipping($order->getSellerTableRateShipping());
            $sellerShippingBlock->setSellerStorePickupShipping($order->getSellerStorePickupShipping());
            $sellerShippingBlock->setTemplate('Magetop_SellerShipping::order_info_shipping_info.phtml');
            $html = $observer->getTransport()->getOutput() . $sellerShippingBlock->toHtml();
            $observer->getTransport()->setOutput($html);
        }
    }
}