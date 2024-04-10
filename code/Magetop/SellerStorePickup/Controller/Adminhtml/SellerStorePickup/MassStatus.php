<?php
namespace Magetop\SellerStorePickup\Controller\Adminhtml\SellerStorePickup;
        // NOTE: this method requires authentication

class MassStatus extends \Magetop\SellerStorePickup\Controller\Adminhtml\SellerStorePickup
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