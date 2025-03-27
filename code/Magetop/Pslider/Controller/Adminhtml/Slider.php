<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Pslider\Controller\Adminhtml;


use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magetop\Pslider\Model\PsliderFactory;
use Magetop\Pslider\Helper\Data;
use Magento\Catalog\Model\CategoryFactory;

abstract class Slider extends Action
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
	protected $resultPageFactory;

	/**
	 * News model factory
	 *
	 * @var \Magetop\Pslider\Model\PsliderFactory
	 */
	protected $_newsFactory;
    protected $_psliderHelper;
    protected $_categoryFactory;
	/**
	 * @param Context $context
	 * @param Registry $coreRegistry
	 * @param PageFactory $resultPageFactory
	 * @param PsliderFactory $newsFactory
	 * @param Data $datahelper
	 * @param CategoryFactory $categoryFactory
	 */
	public function __construct(
		Context $context,
		Registry $coreRegistry,
		PageFactory $resultPageFactory,
		PsliderFactory $newsFactory,
        Data $datahelper,
        CategoryFactory $categoryFactory
	) {
		parent::__construct($context);
		$this->_coreRegistry = $coreRegistry;
		$this->resultPageFactory = $resultPageFactory;
		$this->_newsFactory = $newsFactory;
        $this->_psliderHelper = $datahelper;
        $this->_categoryFactory = $categoryFactory;
	}

    /**
     * Init actions
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb(__('Product Slider Pro'), __('Product Slider Pro'))
            ->addBreadcrumb(__('Manage Item'), __('Manage Item'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Item'));
        return $resultPage;
    }

    /**
	 * News access rights checking
	 *
	 * @return bool
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Magetop_Pslider::pslider');
	}
}
