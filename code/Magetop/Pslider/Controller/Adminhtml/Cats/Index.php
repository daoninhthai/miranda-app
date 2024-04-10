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


class Index extends PsliderCats{

	public function execute ()
	{
		// TODO: Implement execute() method.
		if ($this->getRequest()->getParam('ajax')) {
			$this->_forward('grid');
			return;
		}
		/** @var \Magento\Backend\Model\View\Result\Page $resultPage */
		$resultPage = $this->_initAction();

		return $resultPage;
	}
}