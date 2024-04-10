<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Menupro\Controller\Adminhtml\Menu;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magetop\Menupro\Model\Menu;
use Magetop\Menupro\Helper\Data;

class Edit extends Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * Result page factory
     *
     * @var \Magento\Framework\View\Result\PageFactory
     */
	 
    protected $_resultPageFactory;
	 /**
     * Result page factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory;
     */
	 
	protected $_resultJsonFactory;
	protected $_menuModel;
	protected $_menuHelper;
    function __construct
	(
		Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
		JsonFactory $resultJsonFactory,
		Menu $menuModel,
		Data $menuHelper
	)
	{
		parent::__construct($context);
		$this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_menuModel = $menuModel;
		$this->_menuHelper = $menuHelper;
	}

    public function execute()
    {
		$resultJson = $this->_resultJsonFactory->create();
		$menuid = $this->getRequest()->getParam("id");
		if($menuid != ""){
			$menuinfo = $this->_menuModel->load($menuid)->getData();
			
			//Check if menu type is category and use category name as menu title
			if ($menuinfo['type'] == 4 && $menuinfo['use_category_title'] == 2) {
				$categoryTitle = $this->_menuHelper->getCategoryTitle($menuinfo['url_value']);
				$menuinfo['title'] = $categoryTitle;
			}
		}

		return $resultJson->setData($menuinfo);
    }
	/**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magetop_Menupro::add_new_item');
    } 
}
