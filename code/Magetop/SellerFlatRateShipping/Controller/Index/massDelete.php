<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Multiple_Flat_Rate_Shipping
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerFlatRateShipping\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magetop\SellerFlatRateShipping\Model\SellerFlatRateShippingFactory;
use Magento\Customer\Model\Session;

class massDelete extends \Magento\Framework\App\Action\Action
{
	protected $_customerFactory;
	protected $_mkHelperMail;
	protected $_SellerFlatRateShippingFactory;
    protected $_customerSession;
    
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
    */
    public function __construct(
        Context $context,
		SellerFlatRateShippingFactory $SellerFlatRateShippingFactory,
        Session $customerSession,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
		$this->_SellerFlatRateShippingFactory = $SellerFlatRateShippingFactory;
        $this->_customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
    }
    
    public function execute()
    {
		try{
			$post = $this->getRequest()->getPostValue();
            $deleteShip = 0;
            foreach($post['shipping_selected'] as $ship_id){
                $model = $this->_objectManager->create('Magetop\SellerFlatRateShipping\Model\SellerFlatRateShipping');
                $model->load($ship_id);
                if( $model->getSellerId() == $this->_customerSession->getId()){
                    $model->delete();
                    $deleteShip++;
                }
            }
            $msg = __('A total of %1 record(s) have been deleted.', $deleteShip);
            $this->messageManager->addSuccess( $msg );
            $this->_redirect( 'sellerflatrateshipping' );  
		}catch (\Exception $e) {
			$this->messageManager->addError($e->getMessage());
			$this->_redirect( 'sellerflatrateshipping' );
		}	 
    }
}