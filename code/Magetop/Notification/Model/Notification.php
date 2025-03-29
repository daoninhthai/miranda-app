<?php
namespace Magetop\Notification\Model;

use Magento\Framework\Model\AbstractModel;

class Notification extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Magetop\Notification\Model\ResourceModel\Notification');
    }
}
