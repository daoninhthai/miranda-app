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

class SaveDataShippingToOrderObserver implements ObserverInterface
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
        /** @var Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        /** @var OrderInterface $order */
        $order = $observer->getEvent()->getOrder();
        
        $order->setData('seller_flat_rate_shipping', @$quote->getData('seller_flat_rate_shipping'));
        $order->setData('seller_table_rate_shipping', @$quote->getData('seller_table_rate_shipping'));
        $order->setData('seller_store_pickup_shipping', @$quote->getData('seller_store_pickup_shipping'));
        
        return $this;
    }
}