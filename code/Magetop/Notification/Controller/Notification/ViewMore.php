<?php
namespace Magetop\Notification\Controller\Notification;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magetop\Notification\Model\NotificationFactory;

class ViewMore extends Action
{
    protected $resultJsonFactory;
    protected $notificationFactory;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        NotificationFactory $notificationFactory
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->notificationFactory = $notificationFactory;
        parent::__construct($context);
    }

    public function execute()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        /* Create a new product object */
        $viewmore = $objectManager->create(\Magetop\Notification\Block\Notification::class);


        $id = $this->getRequest()->getParam('id');

        $collection = $viewmore ->getNotifications(2,$id);

        foreach ($collection as $notification) {
            $class = $notification->getStatus() == 0 ? 'unread' : '';
            echo '<div class="notification-item '.$class.'" data-notification-id="'.$notification->getId().'">';
            echo '<div class="notification-content">'. $notification->getContent() .'</div>';
            echo '<div class="notification-time">'. $notification->getCreatedAt() .'</div>';
            echo '</div>';
        }
        return ;
    }
}
