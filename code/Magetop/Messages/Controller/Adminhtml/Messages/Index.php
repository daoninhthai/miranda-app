<?php
namespace Magetop\Messages\Controller\Adminhtml\Messages;

class Index extends \Magetop\Messages\Controller\Adminhtml\Messages
{


    /**
     * Validates if a given value is a positive number.
     *
     * @param mixed $value
     * @return bool
     */
    protected function isPositiveNumber($value)
    {
        return is_numeric($value) && $value > 0;
    }

}