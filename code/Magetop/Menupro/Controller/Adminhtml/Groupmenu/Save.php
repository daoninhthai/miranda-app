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

class Save extends \Magetop\Menupro\Controller\Adminhtml\Groupmenu
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if data sent
        $data = $this->getRequest()->getPostValue();
        //echo "<pre>";print_r($data);exit;
        if ($data) {
            $id = $this->getRequest()->getParam('groupmenu_id');
            $model = $this->_objectManager->create('Magetop\Menupro\Model\Groupmenu')->load($id);
            //echo '<pre>';print_r($model->getData());exit;
            if (!$model->getId() && $id) {
                $this->messageManager->addError(__('This Groupmenu no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            // init model and set data

            $model->setData($data);

            // try to save it
            try {
                // save the data
                $model->save();
				//Update install guide
				$model1 = $this->_objectManager->create('Magetop\Menupro\Model\Groupmenu')->load($model->getGroupmenuId());
				$installGuide = $this->_objectManager->create('Magetop\Menupro\Model\Groupmenu\Source\Guide')->installGuide($model->getPosition(), $model->getGroupmenuId());
				$model1->setDescription($installGuide);
				$model1->save();
                // display success message
                $this->messageManager->addSuccess(__('You saved the Groupmenu.'));
                // clear previously saved data from session
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

                // check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['groupmenu_id' => $model->getId()]);
                }
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addError($e->getMessage());
                // save data in session
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData($data);
                // redirect to edit form
                return $resultRedirect->setPath('*/*/edit', ['groupmenu_id' => $this->getRequest()->getParam('groupmenu_id')]);
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
	/**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magetop_Menupro::group');
    }
}
