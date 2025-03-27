<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Vacation
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerVacation\Block;

class View extends \Magento\Framework\View\Element\Template
{
    protected $_vacationFactory; 
    protected $_coreRegistry; 
    protected $_customerSession;
     
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magetop\SellerVacation\Model\SellerVacationFactory $vacationFactory,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->_vacationFactory = $vacationFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession = $customerSession;
        parent::__construct($context, $data);
        
        //get collection of data 
        $data = $this->_vacationFactory->create()->getCollection()->addFieldToFilter('seller_id',$this->_customerSession->getId())->getFirstItem();      
        $this->setCollection($data);
    }
}