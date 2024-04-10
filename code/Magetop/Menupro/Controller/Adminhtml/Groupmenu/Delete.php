<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Menupro\Controller\Adminhtml\Groupmenu;

class Delete extends \Magetop\Menupro\Controller\Adminhtml\Groupmenu
{
    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam('groupmenu_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create('Magetop\Menupro\Model\Groupmenu');
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccess(__('You deleted the menupro.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['groupmenu_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addError(__('We can\'t find a menupro to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magetop_Menupro::add_new_group');
    }
}
