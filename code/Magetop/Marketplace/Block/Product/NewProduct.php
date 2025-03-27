<?php
/**
 * @author      Magetop Developer (Hau)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Block\Product;
class NewProduct extends \Magento\Framework\View\Element\Template{
	
	protected $_magetopData;
	
	public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,        
		\Magetop\Marketplace\Helper\Data $magetopData,
        array $data = []		
	){		
		parent::__construct($context, $data);
		$this->_magetopData=$magetopData;
	}
	
	public function getOptionSetGroup(){
		$setOptionArray=$this->_magetopData->getOptionSetGroup();
		return $setOptionArray;
	}
}