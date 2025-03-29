<?php
namespace Magetop\Notification\Controller\Notification;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magetop\Notification\Block\Notification;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Viewmore extends Action
{
    protected $jsonFactory;
    protected $notificationBlock;
    protected $timezone;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        Notification $notificationBlock,
        TimezoneInterface $timezone
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->notificationBlock = $notificationBlock;
        $this->timezone = $timezone;
        parent::__construct($context);
    }

    public function execute()
    {
        $result = $this->jsonFactory->create();
        $sellerId = $this->getRequest()->getParam('id');
        $page = $this->getRequest()->getParam('page', 2); // Mặc định trang 2 khi click "View More"

        if (!$sellerId) {
            return $result->setData([
                'success' => false,
                'message' => 'Seller ID is required',
                'html' => '',
                'count' => 0,
                'has_more' => false
            ]);
        }

        $resultData = $this->notificationBlock->getNotifications($sellerId, $page);
        $html = '';

        foreach ($resultData['items'] as $notification) {
            $status = $notification->getStatus();
            $html .= '<div class="notification-item ' . ($status == 0 ? 'unread' : '') . '"
                     data-notification-id="' . $notification->getId() . '">';
            $html .= '<div class="notification-content">' . $notification->getContent() . '</div>';
            $html .= '<div class="notification-time">' .
                $this->timezone->formatDateTime(
                    $notification->getCreatedAt(),
                    \IntlDateFormatter::MEDIUM,
                    \IntlDateFormatter::SHORT
                ) . '</div>';
            $html .= '</div>';
        }

        return $result->setData([
            'success' => true,
            'html' => $html,
            'count' => count($resultData['items']),
            'has_more' => $resultData['has_more'],
            'unread_count' => $this->notificationBlock->getUnreadCount($sellerId)
        ]);
    }
}
