<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Menupro\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magetop\Menupro\Model\Menu;

class Deletemenu extends Action
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
		$ids = $this->getRequest()->getParam("ids");
		try {
			$temp = explode(",", $ids);
			foreach ($temp as $value){
				if($value != ""){
					$id = explode('-', $value);
					$model= $this->_menuModel->load($id[1]);
					$model->delete();
				}
			}
			return $resultJson->setData(['mess'=> 'Ok']);	
		} catch (Exception $e) {
			return $resultJson->setData(['mess'=> 'ERROR WHEN TRYING TO DELETE MENU']);
		}		
    }
}
