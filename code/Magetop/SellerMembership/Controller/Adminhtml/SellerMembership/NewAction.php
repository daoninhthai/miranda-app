<?php
namespace Magetop\SellerMembership\Controller\Adminhtml\SellerMembership;

class NewAction extends \Magetop\SellerMembership\Controller\Adminhtml\SellerMembership
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