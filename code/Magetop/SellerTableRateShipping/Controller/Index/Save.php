<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Multiple_Table_Rate_Shipping
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerTableRateShipping\Controller\Index;
 
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magetop\SellerTableRateShipping\Model\SellerTableRateShippingFactory;
use Magento\Customer\Model\Session;

class Save extends \Magento\Framework\App\Action\Action
{
	protected $_customerFactory;
	protected $_mkHelperMail;
	protected $_SellerTableRateShippingFactory;
    protected $_customerSession;
    
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
    */
    public function __construct(
        Context $context,
		SellerTableRateShippingFactory $SellerTableRateShippingFactory,
        Session $customerSession,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
		$this->_SellerTableRateShippingFactory = $SellerTableRateShippingFactory;
        $this->_customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
    }
    
    public function execute()
    {
		try{
    		$post = $this->getRequest()->getPostValue();
            $model = $this->_objectManager->create('Magetop\SellerTableRateShipping\Model\SellerTableRateShipping');
            if(@$post['shipping_id']){
                $model->load($post['shipping_id']);
            }
            $model->setData('seller_id', $this->_customerSession->getId());
            $model->setData('title', $post['title']);
            $model->setData('type', $post['type']);
            $model->setData('price', $post['price']);
            $model->setData('country_code', $post['country_code']);
            $model->setData('region_id', $post['region_id']);
            $model->setData('zip_from', $post['zip_from']);
            $model->setData('zip_to', $post['zip_to']);
            $model->setData('weight_from', $post['weight_from']);
            $model->setData('weight_to', $post['weight_to']);
            $model->setData('free_shipping', $post['free_shipping']);
            $model->setData('free_shipping', $post['free_shipping']);
            $model->setData('sort_order', $post['sort_order']);
            $model->setData('status', $post['status']);
            $model->save();
            
            $msg = __('You saved successfully.');
            $this->messageManager->addSuccess( $msg );
            $this->_redirect( 'sellertablerateshipping' );            
		}catch (\Exception $e) {
			$this->messageManager->addError($e->getMessage());                                 
			$this->_redirect( 'sellertablerateshipping' );
		}	 
    }
}