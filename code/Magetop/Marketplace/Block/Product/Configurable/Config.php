<?php
/**
 * @author      Magetop Developer (Hau)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Block\Product\Configurable;

use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Config extends  \Magento\Framework\View\Element\Template
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
	
    /**
     * @var Configurable
     */
    protected $configurableType;	
	
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $coreRegistry,
		Configurable $configurableType,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
		$this->configurableType = $configurableType;
        parent::__construct($context, $data);
    }
	
    /**
     * @return bool
     */
    public function isHasVariations()
    {
		//echo '<pre>';print_r(get_class($this->getProduct()));die;
        return ($this->getProduct()->getTypeId() === Configurable::TYPE_CODE || $this->getRequest()->has('attributes'))&& $this->getProduct()->getId();
            //&& $this->configurableType->getUsedProducts($this->getProduct());
    }
    /**
     * Retrieve currently edited product object
     *
     * @return Product
     */
    public function getProduct()
    {
		return $this->_coreRegistry->registry('current_product');
    }

    /**
     * @return bool
     */
    public function isConfigurableProduct()
    {
        return $this->getProduct()->getTypeId() === Configurable::TYPE_CODE || $this->getRequest()->has('attributes');
    }
}
