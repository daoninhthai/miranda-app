<?php
namespace Magetop\SellerDHLShipping\Observer;

use Magento\Framework\Event\Manager;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManager;

class CreateShipmentObserver implements ObserverInterface
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
    * @param \Magento\Framework\Event\Manager $eventManager
    * @param \Magento\Framework\ObjectManagerInterface $objectManager
    */
    public function __construct(
        \Magento\Framework\Event\Manager $eventManager,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_eventManager = $eventManager;
        $this->_objectManager = $objectManager;
    }

    /**
     * when shipment generates from seller panel
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $request = new \Magento\Framework\DataObject();
        $orderId = $observer->getOrderId();
        $request->setOrderId($orderId);
        $this->_objectManager->create('Magetop\SellerDHLShipping\Model\Carrier')->_doShipmentRequest($request);
    }
}