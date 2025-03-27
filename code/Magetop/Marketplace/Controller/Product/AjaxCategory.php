<?php
/**
 * @author      Magetop Developer (Hau)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Controller\Product;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Json\Helper\Data;

class AjaxCategory extends \Magetop\Marketplace\Controller\Product\Account{
    
	protected $resultJsonFactory;	    
	
	public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($context, $customerSession);
    }

	public function execute(){
		$categoryId=(int)$this->getRequest()->getParam('categoryid');
		$level=(int)$this->getRequest()->getParam('level')+1;
		$resultPage = $this->_objectManager->get('Magento\Framework\View\Result\PageFactory')->create();
		$resultPage->addHandle('MAGETOP_MARKETPLACE_CATALOG_CATEGORY');
		$result['content'] = $resultPage->getLayout()->renderElement('magetop_marketplace.ajax.category');					
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result)
        );
	}	
}