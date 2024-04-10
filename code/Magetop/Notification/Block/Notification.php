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

    public function getNotifications($sellerId, $page = 1, $pageSize = 5)
    {
        if (!$sellerId) {
            return [
                'items' => [],
                'total' => 0,
                'has_more' => false
            ];
        }

        $collection = $this->_notificationFactory->create()->getCollection()
            ->addFieldToFilter('seller_id', $sellerId)
            ->setOrder('status', 'ASC') // Ưu tiên status = 0 (chưa đọc) lên đầu
            ->setOrder('created_at', 'DESC'); // Sau đó sắp xếp theo thời gian

        $total = $collection->getSize();
        $collection->setPageSize($pageSize)
            ->setCurPage($page);

        return [
            'items' => $collection->getItems(),
            'total' => $total,
            'has_more' => ($page * $pageSize) < $total
        ];
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
