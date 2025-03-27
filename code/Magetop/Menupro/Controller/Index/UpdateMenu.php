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
use Magetop\Menupro\Model\Menu;

class UpdateMenu extends Action
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
		JsonFactory $resultJsonFactory,
		Menu $menuModel
	)
	{
		parent::__construct($context);
		$this->_resultJsonFactory = $resultJsonFactory;
		$this->_menuModel = $menuModel;
	}

    public function execute()
    {
		$resultJson = $this->_resultJsonFactory->create();
		$saveString=$_POST['saveString'];
		$menu=explode(",", $saveString);
		try {
			$position=0;
			foreach($menu as $value){
				if($value!=""){
					$temp=explode("-", $value);
					$id=$temp[0];
					$groupid=$temp[1];
					$parentid=$temp[2];
					/*Update menu*/
					$model=$this->_menuModel->load($id);
					$model->setParentId($parentid);
					$model->setGroupmenuId($groupid);
					$model->setPosition($position);
					$model->save();
					$position++;
				}
			}
			return $resultJson->setData(['mess'=> 'Ok']);
		} catch (Exception $e) {
			return $resultJson->setData(['mess'=> 'ERROR WHEN TRYING TO SAVE MENU']);
		}
    }
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magetop_Menupro::add_new_item');
    }
}
