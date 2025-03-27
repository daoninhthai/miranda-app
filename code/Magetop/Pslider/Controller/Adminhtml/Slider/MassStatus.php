<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Pslider\Controller\Adminhtml\Slider;


use Magetop\Pslider\Controller\Adminhtml\Slider;

class MassStatus extends Slider{
	public function execute ()
	{
		$status = $this->getRequest()->getParam('status');
		$list = $this->getRequest()->getParam('pslider_id');
		foreach ($list as $item) {
			try {
				/** @var $newsModel \Magetop\Pslider\Model\Pslider */
				$newsModel = $this->_newsFactory->create();
				$newsModel->load($item)->setStatus($status)->save($item);
			} catch (\Exception $e) {
				$this->messageManager->addError($e->getMessage());
			}
		}
		if (count($list)) {
			$this->messageManager->addSuccess(
				__('A total of %1 record(s) status were changed.', count($list))
			);
		}

		$this->_redirect('*/*/index');
	}
}