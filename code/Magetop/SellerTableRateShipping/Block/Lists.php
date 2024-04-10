<?php
namespace Magetop\SellerTableRateShipping\Block;
class Lists extends \Magento\Framework\View\Element\Template
{
    protected $_gridFactory; 
            
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\App\ResourceConnection $resource,
        \Magetop\SellerTableRateShipping\Model\SellerTableRateShippingFactory $gridFactory,              
		\Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->_gridFactory = $gridFactory;             
		$this->_customerSession = $customerSession;
		$this->_resource = $resource;
        parent::__construct($context, $data);
                
        //get collection of data 
        $collection = $this->_gridFactory->create()->getCollection();
		$collection->getSelect()->where('main_table.seller_id=?', $this->_customerSession->getId());
        $collection->setOrder('id', 'ASC' );
        $this->setCollection($collection);
        $this->pageConfig->getTitle()->set(__('Seller Table Rate Shipping'));
    }
    
	public function getCurentUserId()
    {
		return $this->_customerSession->getId();
	}
  
    protected function _prepareLayout()
    {
        $collection = $this->getCollection();
        parent::_prepareLayout();
        if ($collection) {
            // create pager block for collection
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager','my.custom.pager');
            $pager->setAvailableLimit(array(5=>5,10=>10,20=>20,'30'=>'30')); 
            $pager->setCollection($collection);
            $this->setChild('pager', $pager);
            $collection->load();
        }
        return $this;
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }  

    public function getSellerTableRateShippingInformation()
    {
        return $this->_coreRegistry->registry('sellertablerateshippingData');
    }
}