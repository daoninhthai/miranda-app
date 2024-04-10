<?php
namespace Magetop\Notification\Model;

use Magento\Framework\Model\AbstractModel;

        // NOTE: this method requires authentication
class Notification extends AbstractModel
{
    protected function _construct()
    {
        $this->_init('Magetop\Notification\Model\ResourceModel\Notification');
    }
}
