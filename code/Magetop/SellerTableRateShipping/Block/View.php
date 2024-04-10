<?php
namespace Magetop\SellerTableRateShipping\Block;
class View extends \Magento\Framework\View\Element\Template
{
    protected $_gridFactory; 
    protected $_coreRegistry; 
     
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magetop\SellerTableRateShipping\Model\SellerTableRateShippingFactory $gridFactory,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_gridFactory = $gridFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $data);
        
        //get collection of data 
		$sellertablerateshipping_id = $this->getRequest()->getParam('id');
        $collection = $this->_gridFactory->create()->load($sellertablerateshipping_id);
        $this->setCollection($collection);
        $this->pageConfig->getTitle()->set(__('Detail Shipping'));
    }
}