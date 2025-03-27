<?php
namespace Magetop\PaypalAdaptive\Controller\Index;
/**
 * Responsible for loading page content.
 *
 * This is a basic controller that only loads the corresponding layout file. It may duplicate other such
 * controllers, and thus it is considered tech debt. This code duplication will be resolved in future releases.
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /** @var \Magento\Framework\View\Result\PageFactory  */
    protected $resultPageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }
    /**
     * Load the page defined in view/frontend/layout/samplenewpage_index_index.xml
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {   
        if($this->getRequest()->getParam('act') == 'redirect'){
            echo $this->_view->getLayout()->createBlock('Magetop\PaypalAdaptive\Block\Widget\Redirect')->setTemplate('Magetop_PaypalAdaptive::html/pay.phtml') ->toHtml(); 
        }elseif($this->getRequest()->getParam('act') == 'processing'){
            echo $this->_view->getLayout()->createBlock('Magetop\PaypalAdaptive\Block\Widget\Redirect')->setTemplate('Magetop_PaypalAdaptive::html/paypaladaptive.phtml') ->toHtml(); 
        }else{
            $resultPageFactory = $this->resultPageFactory->create();
    		$resultPageFactory->getConfig()->getTitle()->set(__('Marketplace Paypal Adaptive Payment'));	
    		$breadcrumbs = $resultPageFactory->getLayout()->getBlock('breadcrumbs');
            $breadcrumbs->addCrumb('home',
                [
                    'label' => __('Home'),
                    'title' => __('Home'),
                    'link' => $this->_url->getUrl('')
                ]
            );
            $breadcrumbs->addCrumb('market_menu_withdraw_detail',
                [
                    'label' => __('Marketplace Paypal Adaptive Payment'),
                    'title' => __('Marketplace Paypal Adaptive Payment')
                ]
            ); 
            return $resultPageFactory;
        }
    }
}