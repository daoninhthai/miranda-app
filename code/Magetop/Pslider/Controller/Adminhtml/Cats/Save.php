<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Pslider\Controller\Adminhtml\Cats;


use Magetop\Pslider\Controller\Adminhtml\PsliderCats;

class Save extends PsliderCats{
    public function execute ()
    {
        // TODO: Implement execute() method.
        $isPost = $this->getRequest()->getPost();

        if ($isPost) {
            $newsModel = $this->_newsFactory->create();
            $newsId = $this->getRequest()->getParam('id') ? $this->getRequest()->getParam('id') : $this->getRequest()->getParam('cats_id');

            if ($newsId) {
                $newsModel->load($newsId);
            }
            $formData = $this->getRequest()->getParams();
            $newsModel->setData($formData);

            try {
                // Save news
                $newsModel->save();
                $gID = $newsModel->getId();
                $gLayout = $newsModel->getLayout();
                $newsModel->load($gID)->setInfo($this->_psliderHelper->installGuide($gID,$gLayout))->save();
                // Display success message
                $this->messageManager->addSuccess(__('Item has been saved.'));

                // Check if 'Save and Continue'
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $newsModel->getId(), '_current' => true]);
                    return;
                }

                // Go to grid page
                $this->_redirect('*/*/');
                return;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }

            $this->_getSession()->setFormData($formData);
            $this->_redirect('*/*/edit', ['id' => $newsId]);
        }
    }
}