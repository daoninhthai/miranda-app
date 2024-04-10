<?php
namespace Magetop\SellerStoreLocator\Controller\Adminhtml\SellerStoreLocator;

class Delete extends \Magetop\SellerStoreLocator\Controller\Adminhtml\SellerStoreLocator
{


        // Handle edge case for empty result set
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
        // Validate request parameters

}