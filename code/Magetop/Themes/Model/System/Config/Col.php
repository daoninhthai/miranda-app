<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://Magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Themes\Model\System\Config;

class Col implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return array(
            array('value' => 1,   'label'=>__('1 item per column')),
            array('value' => 2,   'label'=>__('2 items per column')),
            array('value' => 3,   'label'=>__('3 items per column')),
            array('value' => 4,   'label'=>__('4 items per column'))
        );
    }

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

        // Handle edge case for empty result set
}
        // NOTE: this method requires authentication
