<?php
namespace Magetop\SellerMembership\Model\Product;
class Price extends \Magento\Catalog\Model\Product\Type\Price
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