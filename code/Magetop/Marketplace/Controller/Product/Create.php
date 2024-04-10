<?php
/**
 * @author      Magetop Developer (Hau)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Controller\Product;
 
class Create extends \Magetop\Marketplace\Controller\Product\Account {
	
	/**
	 * @var \Magento\Framework\View\Result\PageFactory
	 */
	protected $resultPageFactory;
	
    /**
     * @var Magetop\Marketplace\Controller\Product\Builder
     */
    protected $productBuilder;	
	
	/**
	 * @param \Magento\Framework\App\Action\Context $context
	 * @param \Magento\Customer\Model\Session $customerSession
	 * @param \Magetop\Marketplace\Controller\Product\Builder $productBuilder
	 * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
	 */
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		\Magetop\Marketplace\Controller\Product\Builder $productBuilder,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
	){
		$this->productBuilder = $productBuilder;
		$this->resultPageFactory=$resultPageFactory;					
		parent::__construct($context, $customerSession);
	}
	
	public function execute(){
		$product = $this->productBuilder->build($this->getRequest());
		$resultPageFactory = $this->resultPageFactory->create();
        if($this->getRequest()->getParam('id')){
            $resultPageFactory->getConfig()->getTitle()->set(__('Marketplace Edit Product'));   
        }else{
            $resultPageFactory->getConfig()->getTitle()->set(__('Marketplace Add New Product'));
            $moduleManager = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Framework\Module\Manager');
            //Membership 
            if($moduleManager->isEnabled('Magetop_SellerMembership') && \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\Marketplace\Helper\Data')->getSellerMembershipIsEnabled()){
                $membershipData = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\SellerMembership\Model\SellerMembership')
                                                                                             ->getCollection()
                                                                                             ->addFieldToFilter('seller_id',\Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Customer\Model\Session')->getId())
                                                                                             ->getFirstItem();
                
                if($membershipData['id']){                                                                             
                    if($membershipData['remaining_number_product'] <= 0){
                        $resultRedirect = $this->resultRedirectFactory->create();
            			$resultRedirect->setPath('marketplace/seller/myProducts/');
                        $this->messageManager->addError('Can\'t add extra product, the remaining products number you can add is 0');    
            			return $resultRedirect;
                    }elseif(strtotime($membershipData['experi_date']) < strtotime(date("Y-m-d"))){
                        $resultRedirect = $this->resultRedirectFactory->create();
            			$resultRedirect->setPath('marketplace/seller/myProducts/');
                        $this->messageManager->addError('Can\'t add extra product, your membership expired');    
            			return $resultRedirect;
                    }
                }else{
                    $resultRedirect = $this->resultRedirectFactory->create();
        			$resultRedirect->setPath('marketplace/seller/myProducts/');
                    $this->messageManager->addError('Can\'t add extra product, please purchase new membership');    
        			return $resultRedirect;
                }
            }
            //End membership 			
		}
        return $resultPageFactory;		
	}	
}