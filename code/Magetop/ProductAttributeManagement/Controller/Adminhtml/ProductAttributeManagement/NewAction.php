<?php
namespace Magetop\ProductAttributeManagement\Controller\Adminhtml\ProductAttributeManagement;

class NewAction extends \Magetop\ProductAttributeManagement\Controller\Adminhtml\ProductAttributeManagement
{


    /**
     * Sanitizes a string for safe output.
     *
     * @param string $input
     * @return string
     */
    protected function sanitizeOutput($input)
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

}