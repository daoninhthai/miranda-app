<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Multiple_Ups_Shipping
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerUpsShipping\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magetop\SellerUpsShipping\Model\SellerUpsShippingFactory;
use Magento\Customer\Model\Session;

class Save extends \Magento\Framework\App\Action\Action
{
	protected $_customerFactory;
	protected $_mkHelperMail;
	protected $_SellerUpsShippingFactory;
    protected $_customerSession;
    
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
    */
    public function __construct(
        Context $context,
		SellerUpsShippingFactory $SellerUpsShippingFactory,
        Session $customerSession,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
		$this->_SellerUpsShippingFactory = $SellerUpsShippingFactory;
        $this->_customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
    }
    
    public function execute()
    {
		try{
            if($this->_customerSession->isLoggedIn()){
        		$post = $this->getRequest()->getPostValue();
                $model = $this->_objectManager->create('Magetop\SellerUpsShipping\Model\SellerUpsShipping');
                if(@$post['id']){
                    $model->load($post['id']);
                }
                $model->setData('seller_id', $this->_customerSession->getId());
                $model->setData('enable', $post['enable']);
                $model->setData('access_license_number', $post['access_license_number']);
                $allowed_methods = '';
                foreach($post['allowed_methods'] as $val){
                    $allowed_methods .= $val.',';
                }
                $model->setData('allowed_methods', substr($allowed_methods, 0, -1));
                $model->setData('free_shipping_enable', $post['free_shipping_enable']);
                $model->setData('free_shipping_subtotal', $post['free_shipping_subtotal']);
                $model->setData('free_method', $post['free_method']);
                $model->setData('origin_shipment', $post['origin_shipment']);
                $model->setData('password', $post['password']);
                $model->setData('pickup', $post['pickup']);
                $model->setData('username', $post['username']);
                $model->setData('negotiated_active', $post['negotiated_active']);
                $model->setData('shipper_number', $post['shipper_number']);
                $model->setData('sallowspecific', $post['sallowspecific']);
                $specificcountry = '';
                if(@$post['specificcountry']){
                    foreach($post['specificcountry'] as $val){
                        $specificcountry .= $val.',';
                    }
                    $model->setData('specificcountry', substr($specificcountry, 0, -1));            
                }
                $model->save();
                
                $msg = __('You saved successfully.');
                $this->messageManager->addSuccess( $msg );
            }
            $this->_redirect( 'sellerupsshipping' );            
		}catch (\Exception $e) {
			$this->messageManager->addError($e->getMessage());                                 
			$this->_redirect( 'sellerupsshipping' );
		}	 
    }
}