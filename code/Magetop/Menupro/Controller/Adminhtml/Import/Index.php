<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2013-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Menupro\Controller\Adminhtml\Import;
use Magento\Framework\App\Filesystem\DirectoryList;
class Index extends \Magetop\Menupro\Controller\Adminhtml\Action
{
    /**
     * Init page
     *
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->setActiveMenu('Magetop_Menupro::menupro_menu')
            ->addBreadcrumb(__('Menupro'), __('Menupro'))
            ->addBreadcrumb(__('Menupro'), __('Menupro'));
        return $resultPage;
    }
	/**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        return $resultPage;
    }
	/**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magetop_Menupro::import_setting');
    } 
}
