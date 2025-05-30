<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Membership
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerMembership\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
	/**
     * @var \Magento\Customer\Model\Session
     */
	protected $_customerSession;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Customer\Model\Session $customerSession
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        $isseller = $this->_objectManager->get('Magetop\Marketplace\Helper\Data')->checkIsSeller();
        if($isseller){	
    		$customerSession = $this->_customerSession;
    		if(!$customerSession->isLoggedIn())
    		{
    			$this->_redirect('marketplace');
    		}
            $resultPageFactory = $this->resultPageFactory->create();
    		$resultPageFactory->getConfig()->getTitle()->set(__('Seller Membership'));	
    		if($breadcrumbs = $resultPageFactory->getLayout()->getBlock('breadcrumbs')){
                $breadcrumbs->addCrumb('home',
                    [
                        'label' => __('Market Place'),
                        'title' => __('Market Place'),
                        'link' => $this->_url->getUrl('')
                    ]
                );
                $breadcrumbs->addCrumb('market_menu_seller_membership',
                    [
                        'label' => __('Seller Membership'),
                        'title' => __('Seller Membership')
                    ]
                ); 
            }            
            return $resultPageFactory;
        }else{
            $this->_redirect('marketplace/seller/become');
        }
    } 
}