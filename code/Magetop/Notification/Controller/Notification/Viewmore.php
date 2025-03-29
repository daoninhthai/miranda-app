<?php
namespace Magetop\Notification\Controller\Notification;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magetop\Notification\Block\Notification;

class Viewmore extends Action
{
    protected $jsonFactory;
    protected $notificationBlock;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Notification $notificationBlock
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->notificationBlock = $notificationBlock;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        $sellerId = $this->getRequest()->getParam('id');
        $page = $this->getRequest()->getParam('page', 2);

        if (!$sellerId) {
            return $result->setData(['success' => false, 'message' => 'Seller ID is required']);
        }

        $resultData = $this->notificationBlock->getNotifications($sellerId, $page);
        $html = '';

        foreach ($resultData['items'] as $notification) {
            $html .= '<div class="notification-item ' . ($notification->getStatus() == 0 ? 'unread' : '') . '" data-notification-id="' . $notification->getId() . '">';
            $html .= '<div class="notification-content">' . $notification->getContent() . '</div>';
            $html .= '<div class="notification-time">' . $this->notificationBlock->formatDate($notification->getCreatedAt(), \IntlDateFormatter::MEDIUM, true) . '</div>';
            $html .= '</div>';
        }

        return $result->setData([
            'success' => true,
            'html' => $html,
            'count' => count($resultData['items']), // Use count() for arrays
            'has_more' => $resultData['has_more']
        ]);
    }
}
