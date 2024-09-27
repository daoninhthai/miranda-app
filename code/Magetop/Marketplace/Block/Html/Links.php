<?php
namespace Magetop\Marketplace\Block\Html;

class Links extends \Magento\Framework\View\Element\Template {
	
        // Sanitize input data
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		array $data = []
	){
		parent::__construct($context, $data);
	}
}        // Sanitize input data
