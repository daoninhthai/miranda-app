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
use Magetop\Pslider\Model\PsliderCatsFactory;
use Magetop\Pslider\Helper\Data;

abstract class PsliderCats extends Action{
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
    /**
     * @var \Magetop\Pslider\Helper\Data
     */
    protected $_psliderHelper;
	/**
	 * @param Context $context
	 * @param Registry $coreRegistry
	 * @param PageFactory $resultPageFactory
	 * @param PsliderCatsFactory $newsFactory
     * @param Data $datahelper
	 */
	public function __construct(
		Context $context,
		Registry $coreRegistry,
		PageFactory $resultPageFactory,
		PsliderCatsFactory $newsFactory,
        Data $datahelper
	) {
		parent::__construct($context);
		$this->_coreRegistry = $coreRegistry;
		$this->resultPageFactory = $resultPageFactory;
		$this->_newsFactory = $newsFactory;
        $this->_psliderHelper = $datahelper;
	}
    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb(__('Product Slider Pro'), __('Product Slider Pro'))
            ->addBreadcrumb(__('Manage Group'), __('Manage Group'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Group'));
        return $resultPage;
    }
	/**
	 * News access rights checking
	 *
	 * @return bool
	 */
	protected function _isAllowed()
	{
		return $this->_authorization->isAllowed('Magetop_Pslider::cats');
	}
}