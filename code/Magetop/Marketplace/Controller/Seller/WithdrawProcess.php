<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Controller\Seller;

class WithdrawProcess extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
	/**
     * @var \Magento\Customer\Model\Session
     */
	protected $_customerSession;
    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Customer\Model\Session $customerSession
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
		$isseller = $this->_objectManager->get('Magetop\Marketplace\Helper\Data')->checkIsSeller();
        if($isseller){	
    		$customerSession = $this->_customerSession;
    		if(!$customerSession->isLoggedIn())
    		{
    			$this->_redirect('marketplace');
    		}
            $data = $this->getRequest()->getPost();
            $model = $this->_objectManager->create('Magetop\Marketplace\Model\Transactions');
            $model->setData('seller_id', \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Customer\Model\Session')->getId());
            $model->setData('transaction_id', $this->generateRandomString(4));
            $model->setData('payment_id', $data['payment_id']);
            $model->setData('payment_email', $data['payment_email']);
            $model->setData('payment_additional', $data['payment_additional']);
            $model->setData('transaction_amount', $data['transaction_amount']);
            $model->setData('amount_paid', $data['amount_paid']);
            $model->setData('amount_fee', $data['amount_fee']);
            $model->setData('created_at', date('Y-m-d H:i:s',\Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Framework\Stdlib\DateTime\Timezone')->scopeTimeStamp()));
            $model->setData('paid_status', 1);
            $model->save();
            $msg = __('The withdrawal has been request successfully.');
            $this->messageManager->addSuccess( $msg );
            //send mail
            $data['seller_id'] = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Customer\Model\Session')->getId();                        
            $this->_objectManager->create('Magetop\Marketplace\Helper\EmailSeller')->sendRequestWithdrawEmailToSeller($data);
            $this->_objectManager->create('Magetop\Marketplace\Helper\EmailSeller')->sendRequestWithdrawEmailToAdmin($data);                                 
            $url_redirect = 'marketplace/seller/myTransactions';
            $this->_redirect(  $url_redirect );
        }else{
            $this->_redirect('marketplace/seller/become');
        }
    } 
    //create random id
    public function generateRandomString($length)
	{
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
	    
		$randomString1 = '';
		$randomString2 = '';
		for ($i = 0; $i < $length; $i++) {
            $randomString1 .= $characters[rand(0, $charactersLength - 1)];
		}
		for ($i = 0; $i < $length; $i++) {
            $randomString2 .= $characters[rand(0, $charactersLength - 1)];
		}
        
        $str = 'MK';
		$str = $str.'-'.$randomString1.'-'.$randomString2;
		
	    return $str;
	}
}