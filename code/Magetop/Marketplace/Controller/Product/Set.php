<?php 
/**
 * @author      Magetop Developer (Hau)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Controller\Product;

class Set extends \Magetop\Marketplace\Controller\Product\Account{
	
	protected $resultPageFactory;	
	protected $_customerSession;	
	
	public function __construct(	
		\Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
	){
		$this->resultPageFactory=$resultPageFactory;
		parent::__construct($context, $customerSession);
	}
	
    public function execute()
    {
        $isseller=$this->_objectManager->get('Magetop\Marketplace\Helper\Data')->checkIsSeller();
        if($isseller){
			$resultPageFactory = $this->resultPageFactory->create();
			$resultPageFactory->getConfig()->getTitle()->set(__('Marketplace Add New Product'));
			if($breadcrumbs = $resultPageFactory->getLayout()->getBlock('breadcrumbs')){
				$breadcrumbs->addCrumb('home',
					[
						'label' => __('Market Place'),
						'title' => __('Market Place'),
						'link' => $this->_url->getUrl('')
					]
				);
				$breadcrumbs->addCrumb('market_menu_withdraw_detail',
					[
						'label' => __('New Product'),
						'title' => __('New Product')
					]
				); 
			}			
			return $resultPageFactory;
        }else{
            $resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setPath('marketplace/seller/become');
			return $resultRedirect;
        }		
	}
}