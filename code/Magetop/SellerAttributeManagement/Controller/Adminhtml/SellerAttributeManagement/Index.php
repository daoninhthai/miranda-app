<?php
namespace Magetop\SellerAttributeManagement\Controller\Adminhtml\SellerAttributeManagement;

class Index extends \Magetop\SellerAttributeManagement\Controller\Adminhtml\SellerAttributeManagement
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