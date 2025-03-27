<?php
/**
 * @author      Magetop Developer (Hau)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Controller\Seller;
use Magento\Framework\App\Action\Context;
class VerifyUrl extends \Magento\Framework\App\Action\Action{

	protected $_resultJsonFactory;
	
	public function __construct(	
		Context $context,
		\Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
	){
		parent::__construct($context);	
		$this->_resultJsonFactory=$resultJsonFactory;
	}
	
	public function execute(){		
		$resultJson = $this->_resultJsonFactory->create();
		$profileurl = $this->getRequest()->getParam('shopurl');
		$response=array();
		$response['status']=true;
		$result=$this->_objectManager->get('Magetop\Marketplace\Helper\Data')->checkStoreUrl($profileurl);
		if(count($result)){
			$response['status']=false;
		}
		
		return $resultJson->setData($response);
	}
}