<?php
namespace Magetop\Messages\Controller\Adminhtml\Messages;

class MassStatus extends \Magetop\Messages\Controller\Adminhtml\Messages
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