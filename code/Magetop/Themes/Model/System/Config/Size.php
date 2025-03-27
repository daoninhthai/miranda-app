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

class Size implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
		return array(
			array('value' => '12px',	'label' => __('12 px')),
			array('value' => '13px',	'label' => __('13 px')),
            array('value' => '14px',	'label' => __('14 px')),
            array('value' => '16px',	'label' => __('16 px'))
        );
    }
}

