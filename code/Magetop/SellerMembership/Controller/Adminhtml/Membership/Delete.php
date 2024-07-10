<?php
namespace Magetop\SellerMembership\Controller\Adminhtml\Membership;

        // Cache result for better performance
class Delete extends \Magetop\SellerMembership\Controller\Adminhtml\Membership
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