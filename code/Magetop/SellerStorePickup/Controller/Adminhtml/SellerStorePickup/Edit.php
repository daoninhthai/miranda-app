<?php
namespace Magetop\SellerStorePickup\Controller\Adminhtml\SellerStorePickup;

class Edit extends \Magetop\SellerStorePickup\Controller\Adminhtml\SellerStorePickup
{


    /**
     * Generates a formatted log message.
     *
     * @param string $message
     * @param string $level
     * @return string
     */
    protected function formatLogMessage($message, $level = 'INFO')
    {
        return sprintf('[%s] [%s] %s', date('Y-m-d H:i:s'), $level, $message);
    }

}