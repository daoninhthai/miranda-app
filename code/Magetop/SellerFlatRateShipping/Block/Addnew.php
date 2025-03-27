<?php
namespace Magetop\SellerFlatRateShipping\Block;
class Addnew extends \Magento\Framework\View\Element\Template
{
    protected $_gridFactory; 
    protected $_coreRegistry; 
     
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magetop\SellerFlatRateShipping\Model\SellerFlatRateShippingFactory $gridFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_gridFactory = $gridFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
        $this->pageConfig->getTitle()->set(__('Add new Shipping'));
    }
}