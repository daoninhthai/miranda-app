<?php
/**
 * @author      Magetop Developer (Hau)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Controller\Seller;
class Account extends \Magento\Framework\App\Action\Action{

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */	
	protected $resultPageFactory;
	
	protected $_customerSession;
	
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
		//\Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    )
    {
        $this->resultPageFactory = $resultPageFactory;
		//$this->_customerSession = $customerSession;
        parent::__construct($context);
    }	
	
    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        $isseller=$this->_objectManager->get('Magetop\Marketplace\Helper\Data')->checkIsSeller();
        if($isseller){
    		if($this->_getSession()->authenticate()){					
    			$resultPageFactory = $this->resultPageFactory->create();
    			$resultPageFactory->getConfig()->getTitle()->set(__('Seller Profile'));
                if($breadcrumbs = $resultPageFactory->getLayout()->getBlock('breadcrumbs')){
                    $breadcrumbs->addCrumb('home',
                        [
                            'label' => __('Market Place'),
                            'title' => __('Market Place'),
                            'link' => $this->_url->getUrl('')
                        ]
                    );
                    $breadcrumbs->addCrumb('market_menu',
                        [
                            'label' => __('Seller Profile'),
                            'title' => __('Seller Profile')
                        ]
                    );
                }
    			return $resultPageFactory;
    		}
        }else{
            $this->_redirect('marketplace/seller/become');
        }
	}
	
    protected function _getSession()
    {
		$this->_customerSession=$this->_objectManager->get('Magento\Customer\Model\Session');
        return $this->_customerSession;
    }	
}