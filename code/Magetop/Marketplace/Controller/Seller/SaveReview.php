<?php
/**
 * @author      Magetop Developer (Uoc)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Controller\Seller;

class SaveReview extends \Magento\Framework\App\Action\Action
{
	protected $_modelSession;
	protected $_sellersFactory;
	protected $_timezone;
	protected $_reviewsFactory;
	
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
		\Magento\Customer\Model\Session $modelSession,
		\Magetop\Marketplace\Model\SellersFactory $sellersFactory,
		\Magento\Framework\Stdlib\DateTime\Timezone $timezone,
		\Magetop\Marketplace\Model\ReviewsFactory $reviewsFactory
    )
    {
		$this->_modelSession = $modelSession;
		$this->_sellersFactory  = $sellersFactory;
		$this->_timezone = $timezone;
		$this->_reviewsFactory = $reviewsFactory;
        parent::__construct($context);
    }

    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        /*if (!$this->_validateFormKey()) {
            return $this->_redirect('maketplace');
        } */
		$params = $this->getRequest()->getParams();
		if(!$this->_modelSession->isLoggedIn())
		{
			$this->_redirect('marketplace');
		}
		$customerId = $this->_modelSession->getId();
		$sellersModel = $this->_sellersFactory->create();
		$storeurl = isset($params['seller_storeurl']) ? $params['seller_storeurl'] : '';
		$collection = $sellersModel->getCollection()->addFieldToFilter('storeurl',$storeurl);
		$seller = $collection->getFirstItem();
		$okSave = false;
		if($seller && $seller->getId())
		{
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            if($objectManager->create('Magetop\Marketplace\Helper\Data')->getReviewApprovalRequired()){
                $status = 0;
            }else{
                $status = 1;
            }
			$dataSave = array(
				'userid'=>$seller->getUserId(),
				'status'=>$status,
				'user_review_id'=>$customerId,
				'price'=>$params['reivew_price'],
				'value'=>$params['reivew_value'],
				'quality'=>$params['reivew_quanlity'],
				'nickname'=>$params['reivew_nickname'],
				'summary'=>$params['reivew_summary'],
				'review'=>$params['reivew_review'],
				'createdate'=>date('Y-m-d H:i:s',$this->_timezone->scopeTimeStamp()),
			);
			$reviewModel = $this->_reviewsFactory->create();
			try
			{
				$reviewModel->setData($dataSave)->save();
				$okSave = true;
				$this->messageManager->addSuccess(__('You have been reviewed successful.'));
				$this->_redirect($params['currently_url']);
			}
			catch (\Exception $e) {
				$this->messageManager->addError($e->getMessage());
				$this->_redirect($params['currently_url']);
			}
		}
		if(!$okSave)
		{
			$this->_redirect($params['currently_url']);
		}
    } 
}