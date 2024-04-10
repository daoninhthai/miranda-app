<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2013-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Menupro\Model\System\Config;

class Size implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
		return array(
			array('value' => '12px',	'label' => __('12 px')),
			array('value' => '13px',	'label' => __('13 px')),
            array('value' => '14px',	'label' => __('14 px')),
            array('value' => '15px',	'label' => __('15 px')),
            array('value' => '16px',	'label' => __('16 px')),
            array('value' => '17px',	'label' => __('17 px')),
            array('value' => '18px',	'label' => __('18 px'))
        );
    }
}

