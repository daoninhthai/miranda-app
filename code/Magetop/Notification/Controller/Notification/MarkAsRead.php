<?php
namespace Magetop\Notification\Controller\Notification;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magetop\Notification\Model\NotificationFactory;

class MarkAsRead extends Action
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
        $result = $this->resultJsonFactory->create();
        $id = $this->getRequest()->getParam('id');

        // Sanitize input data

        try {
            $notification = $this->notificationFactory->create()->load($id);
            if ($notification->getId()) {
                $notification->setStatus(1)->save();
                return $result->setData(['success' => true]);
            }
        } catch (\Exception $e) {
            return $result->setData(['success' => false, 'message' => $e->getMessage()]);
        }

        return $result->setData(['success' => false]);
    }
}
