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

class Edit extends PsliderCats{
	public function execute ()
	{

        // TODO: Implement execute() method.
		$newsId = $this->getRequest()->getParam('cats_id');
		/** @var \Magetop\Pslider\Model\PsliderCats $model */
		$model = $this->_newsFactory->create();

		if ($newsId) {
			$model->load($newsId);
			if (!$model->getId()) {
				$this->messageManager->addError(__('This item no longer exists.'));
				$this->_redirect('*/*/');
				return ;
			}
		}

		// Restore previously entered form data from session
		$data = $this->_session->getNewsData(true);
		if (!empty($data)) {
			$model->setData($data);
		}
		$this->_coreRegistry->register('pslider_cats', $model);

		/** @var \Magento\Backend\Model\View\Result\Page $resultPage */
		$resultPage = $this->_initAction();
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() . ' - Editting Group' : __('Add New Group'));

		return $resultPage;
	}
}