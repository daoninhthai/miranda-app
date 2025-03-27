<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Assign_Product
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerAssignProduct\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magetop\SellerAssignProduct\Model\SellerAssignProductFactory;
use Magento\Customer\Model\Session;

class massDelete extends \Magento\Framework\App\Action\Action
{
	protected $_customerFactory;
	protected $_mkHelperMail;
	protected $_SellerAssignProductFactory;
    protected $_customerSession;
    
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
    */
    public function __construct(
        Context $context,
		SellerAssignProductFactory $SellerAssignProductFactory,
        Session $customerSession,
        PageFactory $resultPageFactory
		
    ) {
        parent::__construct($context);
		$this->_SellerAssignProductFactory = $SellerAssignProductFactory;
        $this->_customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
    }
    
    public function execute()
    {
		try{
			$post = $this->getRequest()->getPostValue();
            $deletePro = 0;
            foreach($post['product_selected'] as $pro_id){
                $model = $this->_objectManager->create('Magetop\SellerAssignProduct\Model\SellerAssignProduct');
                $model->load($pro_id);
                if( $model->getSellerId() == $this->_customerSession->getId()){
                    $model->delete();
                    $deletePro++;
                }
                
            }
            $msg = __('A total of %1 record(s) have been deleted.', $deletePro);
            $this->messageManager->addSuccess( $msg );
            $this->_redirect( 'sellerassignproduct' );  
		}catch (\Exception $e) {
			$this->messageManager->addError($e->getMessage());
			$this->_redirect( 'sellerassignproduct' );
		}	 
    }
}