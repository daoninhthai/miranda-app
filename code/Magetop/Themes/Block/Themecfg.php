<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://Magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Themes\Block;

class Themecfg extends \Magento\Framework\View\Element\Template
{

    public $_themeCfg;
    public $_time;

    public $_scopeConfig;

	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Stdlib\DateTime\DateTime $time,
        \Magetop\Themes\Helper\Data $_helper,
        array $data = []
	) {
        parent::__construct($context, $data);
        $this->_time  		= $time;
		$this->_themeCfg 	= $_helper->getThemeCfg();
		$this->_scopeConfig = $context->getScopeConfig();
	}
}
