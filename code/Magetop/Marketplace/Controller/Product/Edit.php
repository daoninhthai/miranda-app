<?php
/**
 * @author      Magetop Developer (Hau)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Controller\Product;
 
class Edit extends \Magetop\Marketplace\Controller\Product\Account {
	
    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;
	
	/**
	 * @param \Magento\Framework\App\Action\Context $context
	 * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
	 */
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $customerSession,
		\Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
	){
		parent::__construct($context, $customerSession);
		$this->resultForwardFactory = $resultForwardFactory;
	}
	
	public function execute(){
		$resultForward = $this->resultForwardFactory->create();
		
        if($id=$this->getRequest()->getParam('id')){
            $resultForward->setController('product');
			$param = array('id' => $id);
			if($set = $this->getRequest()->getParam('set')) {
				$param['set'] = $set;
			}
			$resultForward->setParams($param);
			$resultForward->forward('create');
        } else {
            $resultForward->forward('noroute');
		}
        return $resultForward;
	}
}