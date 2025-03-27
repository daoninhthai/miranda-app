<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2013-05-5
 * @@Modify Date: 2017-06-05
 */
/*--------------------*/
namespace Magetop\Menupro\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

class UpdateGroupmenu extends Action
{
	 /**
     * Result page factory
     *
     * @var \Magento\Framework\Controller\Result\JsonFactory;
     */	 
	protected $_resultJsonFactory;
	/**
     * Model menu
     *
     * @var \Magetop\Menupro\Model\Menu;
     */	
	protected $_menuModel;
    function __construct
	(
		Context $context,
		JsonFactory $resultJsonFactory
	)
	{
		parent::__construct($context);
		$this->_resultJsonFactory = $resultJsonFactory;
	}

    public function execute()
    {
		$resultJson = $this->_resultJsonFactory->create();
		$id = $this->getRequest()->getParam('groupmenu_id');
		$_color = $this->getRequest()->getParam('color');
		$_animation = $this->getRequest()->getParam('animation');
		$_responsive = $this->getRequest()->getParam('responsive');
		$subMenuTrigger = $this->getRequest()->getParam('sub_menu_trigger');
		$enableSticky = $this->getRequest()->getParam('enable_sticky');
        $model = $this->_objectManager->create('Magetop\Menupro\Model\Groupmenu');
		if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                return $resultJson->setData(['mess'=> 'This Groupmenu no longer exists.']);
            }
			$model->setColor($_color);
			$model->setAnimation($_animation);
			$model->setResponsive($_responsive);
			$model->setSubMenuTrigger($subMenuTrigger);
			$model->setEnableSticky($enableSticky);
			$model->save();
			return $resultJson->setData(['mess'=> 'Ok']);
        }
		return $resultJson->setData(['mess'=> 'err']);
    }
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magetop_Menupro::add_new_item');
    }
}
