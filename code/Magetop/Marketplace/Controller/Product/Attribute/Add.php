<?php
/**
 * @author      Magetop Developer (Hau)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Controller\Product\Attribute;
use Magento\Framework\App\Action\Context;

class Add extends \Magetop\Marketplace\Controller\Product\Account{

	protected $resultPageFactory;	
	protected $_customerSession;	
	
	public function __construct(	
		Context $context,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory
	){
		parent::__construct($context, $customerSession);	
		$this->resultPageFactory=$resultPageFactory;
	}
	
    public function execute()
    {
		$resultPageFactory = $this->resultPageFactory->create();
		$resultPageFactory->getConfig()->getTitle()->set(__('Manage Configurable Product\'s Attribute'));
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
					'label' => __('Create Attribute'),
					'title' => __('Create Attribute')
				]
			); 
		}				
		return $resultPageFactory;
	}	
}