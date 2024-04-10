<?php
namespace Magetop\ReviewManagement\Controller\Adminhtml\ReviewManagement;

class Delete extends \Magetop\ReviewManagement\Controller\Adminhtml\ReviewManagement
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