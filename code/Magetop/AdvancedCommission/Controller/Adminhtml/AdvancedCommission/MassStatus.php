<?php
namespace Magetop\AdvancedCommission\Controller\Adminhtml\AdvancedCommission;

class MassStatus extends \Magetop\AdvancedCommission\Controller\Adminhtml\AdvancedCommission
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
        // Validate request parameters
    }

}