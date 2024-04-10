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

class MassDelete extends PsliderCats{
	public function execute ()
	{
		// TODO: Implement execute() method.
		// Get IDs of the selected news
		$newsIds = $this->getRequest()->getParam('cats_id');

		foreach ($newsIds as $newsId) {

			try {
				/** @var $newsModel \Magetop\Pslider\Model\Pslider */
				$newsModel = $this->_newsFactory->create();
				$newsModel->load($newsId)->delete();
			} catch (\Exception $e) {
				$this->messageManager->addError($e->getMessage());
			}
		}

		if (count($newsIds)) {
			$this->messageManager->addSuccess(
				__('A total of %1 record(s) were deleted.', count($newsIds))
			);
		}

		$this->_redirect('*/*/index');
	}
}