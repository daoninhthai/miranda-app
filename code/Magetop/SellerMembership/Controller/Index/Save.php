<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Membership
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerMembership\Controller\Index;

class Save extends \Magento\Framework\App\Action\Action
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
    protected $_sellermembershipFactory; 
         
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Customer\Model\Session $customerSession,
        \Magetop\SellerMembership\Model\SellerMembership $sellermembershipFactory        
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
        $this->_sellermembershipFactory = $sellermembershipFactory;           
        parent::__construct($context);
    }

    /**
     * Default customer account page
     *
     * @return void
     */
    public function execute()
    {
        try{
            $isseller = $this->_objectManager->get('Magetop\Marketplace\Helper\Data')->checkIsSeller();
            if($isseller){	
                $customerSession = $this->_customerSession;
                if($customerSession->isLoggedIn()){
                    $data = $this->getRequest()->getPostValue(); 
                    $membership = $this->_sellermembershipFactory;
                    if($data['id']){
                        $membership->load($data['id']);
                    }else{
                        $time = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\Timezone');
                        $membership->setData('created_at', date('Y-m-d H:i:s',$time->scopeTimeStamp()));
                    }
                    $membership->setData('seller_id', $this->_customerSession->getId());
                    $membership->setData('longitude', $data['longitude']);
                    $membership->setData('latitude', $data['latitude']);
                    $membership->setData('zoom', $data['zoom']);
                    $membership->setData('shop_location', $data['shop_location']);
                    $membership->setData('status', $data['status']);
                    $membership->save();
                }
            }  
            $msg = __('You saved successfully.');
            $this->messageManager->addSuccess( $msg );
            $this->_redirect( 'sellermembership/index/view' );  
        }catch (\Exception $e) {    
            $this->messageManager->addError($e->getMessage()); 
            $this->_redirect( 'sellermembership/index/view' );       
        }    
    } 
}