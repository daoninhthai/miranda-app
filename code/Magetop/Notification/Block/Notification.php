<?php
namespace Magetop\Notification\Block;

use Magento\Framework\View\Element\Template;

class Notification extends Template
{
    protected $_notificationFactory;

    public function __construct(
        Template\Context $context,
        \Magetop\Notification\Model\NotificationFactory $notificationFactory,
        array $data = []
    ) {
        $this->_notificationFactory = $notificationFactory;
        parent::__construct($context, $data);
    }

    public function getNotifications($page,$sellerId)
    {
        if (!$sellerId) {
            return [];
        }

        $collection = $this->_notificationFactory->create()->getCollection()
            ->addFieldToFilter('seller_id', $sellerId)
            ->setOrder('created_at', 'DESC')
            ->setPageSize(5) // Lấy 5 thông báo gần nhất
            ->setCurPage($page);
        return $collection;
    }

    public function getUnreadCount($sellerId)
    {
        if (!$sellerId) {
            return 0;
        }

        $collection = $this->_notificationFactory->create()->getCollection()
            ->addFieldToFilter('seller_id', $sellerId)
            ->addFieldToFilter('status', 0);

        return $collection->getSize();
    }
}
