<?php
namespace Magetop\SellerDHLShipping\Observer;

use Magento\Framework\Event\Manager;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManager;

class SalesOrderPlaceAfterObserver implements ObserverInterface
{
    /**
     * @var eventManager
     */
    protected $_eventManager;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $_customerSession;
    /**
     * @var Session
     */
    protected $_session;

    protected $_logger;
    /**
    * @param \Magento\Framework\Event\Manager $eventManager
    * @param \Magento\Framework\ObjectManagerInterface $objectManager
    * @param Magento\Customer\Model\Session $customerSession
    * @param \Magento\Framework\Logger\Monolog $logger
    * @param SessionManager $session
    */
    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Logger\Monolog $logger,
        SessionManager $session
    ) {
        $this->_eventManager = $eventManager;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->_logger = $logger;
        $this->_session = $session;
    }

    /**
     * after place order event handler
     * Distribute Shipping Price for sellers
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $shippingmethod = $order->getShippingMethod();
        $lastOrderId = $observer->getOrder()->getId();
        /*if (@strpos($shippingmethod, 'mpdhl')!==false) {
            $allorderitems=$order->getAllItems();
            $shipmethod=explode('_', $shippingmethod, 2);
            $shippingAll=$this->_session->getShippingInfo();
            foreach ($shippingAll['mpdhl'] as $shipdata) {
                $collection=$this->_objectManager->create('Magetop\Marketplace\Model\Orders')
                            ->getCollection()
                            ->addFieldToFilter('order_id', ['eq'=>$lastOrderId])
                            ->addFieldToFilter('seller_id', ['eq'=>$shipdata['seller_id']])
                            ->getFirstItem();
                if ($collection->getId()) {
                    $collection->setCarrierName($shipdata['submethod'][$shipmethod[1]]['method']);
                    $collection->setShippingCharges($shipdata['submethod'][$shipmethod[1]]['cost']);
                    $collection->save();
                }
            }
            $this->_session->unsShippingInfo();
        }*/
    }
}