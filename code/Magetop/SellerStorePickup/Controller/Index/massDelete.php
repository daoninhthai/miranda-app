<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Store_Pickup
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerStorePickup\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magetop\SellerStorePickup\Model\SellerStorePickupFactory;
use Magento\Customer\Model\Session;

class massDelete extends \Magento\Framework\App\Action\Action
{
	protected $_customerFactory;
	protected $_mkHelperMail;
	protected $_SellerStorePickupFactory;
    protected $_customerSession;
    
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
    */
    public function __construct(
        Context $context,
		SellerStorePickupFactory $SellerStorePickupFactory,
        Session $customerSession,
        PageFactory $resultPageFactory
		
    ) {
        parent::__construct($context);
		$this->_SellerStorePickupFactory = $SellerStorePickupFactory;
        $this->_customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
    }
    
    public function execute()
    {
		try{
			$post = $this->getRequest()->getPostValue();
            $deleteStore = 0;
            foreach($post['storepickup_selected'] as $store_id){
                $model = $this->_objectManager->create('Magetop\SellerStorePickup\Model\SellerStorePickup');
                $model->load($store_id);
                if( $model->getSellerId() == $this->_customerSession->getId()){
                    $model->delete();
                    $deleteStore++;
                }
                
            }
            $msg = __('A total of %1 record(s) have been deleted.', $deleteStore);
            $this->messageManager->addSuccess( $msg );
            $this->_redirect( 'sellerstorepickup/index/liststore/' );  
		}catch (\Exception $e) {
			$this->messageManager->addError($e->getMessage());
			$this->_redirect( 'sellerstorepickup/index/liststore/' );
		}	 
    }
}