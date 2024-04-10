<?php
namespace Magetop\Notification\Model\ResourceModel\Notification;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(
            'Magetop\Notification\Model\Notification',
            'Magetop\Notification\Model\ResourceModel\Notification'
        );
    }
}
