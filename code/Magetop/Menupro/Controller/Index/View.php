<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2013-05-5
 * @@Modify Date: 2017-06-05
 */
/*--------------------*/
namespace Magetop\Menupro\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;

class View extends \Magento\Framework\App\Action\Action
{
	protected $resultPageFactory;
	 /**
     * Result page factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory;
     */

	protected $_resultJsonFactory;
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
		JsonFactory $resultJsonFactory
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
		$resultJson = $this->_resultJsonFactory->create();
	    $groupmenuId = $this->getRequest()->getParam('groupmenuId');
		$products = $this->_view->getLayout()->createBlock('Magetop\Menupro\Block\Menu')->setGroupmenu_id($groupmenuId)->setTemplate('Magetop_Menupro::menupro/view.phtml')->toHtml();
		$response = array('html_result'=>$products);
		return $resultJson->setData($response);			
    }
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magetop_Menupro::add_new_item');
    }
}