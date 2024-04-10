<?php
namespace Magetop\SellerCoupon\Controller\Adminhtml\SellerCoupon;

class Edit extends \Magetop\SellerCoupon\Controller\Adminhtml\SellerCoupon
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