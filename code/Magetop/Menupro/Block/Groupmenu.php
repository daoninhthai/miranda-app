<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Menupro\Block;

use Magento\Framework\View\Element\Template;

class Groupmenu extends Template
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