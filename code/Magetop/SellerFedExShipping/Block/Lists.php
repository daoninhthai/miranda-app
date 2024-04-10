<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Multiple_FedEx_Shipping
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerFedExShipping\Block;

class Lists extends \Magento\Framework\View\Element\Template
{
    protected $_gridFactory; 
    protected $_customerSession;
            
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\App\ResourceConnection $resource,
        \Magetop\SellerFedExShipping\Model\SellerFedExShippingFactory $gridFactory,              
		\Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->_gridFactory = $gridFactory;             
		$this->_customerSession = $customerSession;
		$this->_resource = $resource;
        parent::__construct($context, $data);
    }

    public function getCollection()
    {
        //get collection of data 
        $collection = $this->_gridFactory->create()->getCollection()->addFieldToFilter('seller_id',$this->_customerSession->getId())->getFirstItem();
        $this->setCollection($collection);

        return $collection;
    }
	
	public function getCurentUserId()
    {
		return $this->_customerSession->getId();
	}
}