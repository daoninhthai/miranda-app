<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Menupro\Model\Groupmenu\Source;

class Options
{

    public function toAnimationOption()
    {
        $options = [
						'animation-none' => __('No Animation'),
						'animation-fadeIn' => __('FadeIn'),
						'animation-hingeTop' => __('Hinge Top'),
						'animation-hingeLeft' => __('Hinge Left'),
						'animation-slideUp' => __('Slide Up'),
						'animation-slideDown' => __('Slide Down'),
						'animation-slideLeft' => __('Slide Left'),
						'animation-slideRight' => __('Slide Right'),
						'animation-zoomIn' => __('Zoom In'),
						'animation-pivotTopLeft' => __('Pivot Top-Left'),
						'animation-recoil' => __('Recoil')
					];
        return $options;

    }
	public function toPositionsnOption()
    {
        $options = [
						'menu-creator-pro' => __('Top (default)'),
						'menu-creator-pro menu-creator-pro-left' => __('Left'),
						'menu-creator-pro menu-creator-pro-right' => __('Right'),
						'menu-creator-pro menu-creator-pro-bottom' => __('Bottom'),
						'menu-creator-pro menu-creator-pro-top-fixed' => __('Top Fixed'),
						'menu-creator-pro menu-creator-pro-left-fixed' => __('Left Fixed'),
						'menu-creator-pro menu-creator-pro-right-fixed' => __('Right Fixed'),
						'menu-creator-pro menu-creator-pro-bottom-fixed' => __('Bottom Fixed'),
						'menu-creator-pro menu-creator-pro-accordion' => __('Accordion')
					];
        return $options;

    }
	public function toResponsiveOption()
    {
        $options = [
						'mcp-push-cover' => __('Responsive multi-level push covering (default)'),
						'mcp-push-overlap' => __('Responsive multi-level push overlapping'),
						'menu-creator-pro-rp-switcher' => __('Responses into switcher'),
						'disable' => __('No responsiveness')
					];
        return $options;

    }
	public function toColorOption()
    {
        $options = [
						'#980202' => __('Red 1 (default)'),
						'#7f0101' => __('Red 2'),
						'#824d06' => __('Chocolate'),
						'#6a3f05' => __('SaddleBrown '),
						'#07850d' => __('Green 1'),
						'#056d0b' => __('Green 2'),
						'#0a818b' => __('DeepSkyBlue'),
						'#096b73' => __('DodgerBlue'),
						'#17529f' => __('RoyalBlue 1'),
						'#144788' => __('RoyalBlue 2'),
						'#3a0882' => __('Indigo 1'),
						'#2f076a' => __('Indigo 2'),
						'#830760' => __('MediumVioletRed 1'),
						'#6b064f' => __('MediumVioletRed 2'),
						'#625409' => __('Olive 1'),
						'#4b4007' => __('Olive 2'),
					];
        return $options;
    }
	public function getMobileResponsiveStyles () {
		return array(
			'mcp-push-cover',
			'mcp-push-overlap'
		);
	}
}
