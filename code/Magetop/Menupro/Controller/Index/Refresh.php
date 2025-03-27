<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Menupro\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magetop\Menupro\Model\Menu;
//use Magento\Catalog\Model\Session;
class Refresh extends Action
{
	 /**
     * Result page factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory;
     */	 
	protected $_resultJsonFactory;
	/**
     * Model menu
     *
     * @var \Magetop\Menupro\Model\Menu;
     */	
	protected $_menuModel;
    function __construct
	(
		Context $context,
		//Session $catalogSession,
		JsonFactory $resultJsonFactory,
		Menu $menuModel
	)
	{
		parent::__construct($context);
		//$this->catalogSession = $catalogSession;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_menuModel = $menuModel;
	}

    public function execute()
    {
		$resultJson = $this->_resultJsonFactory->create();
		//$this->catalogSession->setCategoryTree('');
		$message = 'Refresh process done!';
		//Remove all static html file

		$menuObj = $this->_objectManager->create('\Magetop\Menupro\Block\Menu');
		if (!$menuObj->removeAllStaticHtml()) {
			$message = "Refresh process error!";
		} 
		return $resultJson->setData(['message'=>$message]);
    }
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magetop_Menupro::add_new_item');
    }
}
