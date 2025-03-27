<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Vacation
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerVacation\Controller\Index;

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
    protected $_sellervacationFactory; 
         
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Customer\Model\Session $customerSession,
        \Magetop\SellerVacation\Model\SellerVacation $sellervacationFactory        
    )
    {
        $this->resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
        $this->_sellervacationFactory = $sellervacationFactory;           
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
                    $vacation = $this->_sellervacationFactory;
                    if($data['id']){
                        $vacation->load($data['id']);
                        $time = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\Timezone');
                        $vacation->setData('updated_at', date('Y-m-d H:i:s',$time->scopeTimeStamp()));
                        if($vacation->getSellerId() == $this->_customerSession->getId()){
                            $vacation->setData('seller_id', $this->_customerSession->getId());
                            $vacation->setData('vacation_message', $data['vacation_message']);
                            $vacation->setData('date_from', $data['date_from']);
                            $vacation->setData('date_to', $data['date_to']);
                            $vacation->setData('text_add_cart', $data['text_add_cart']);
                            $vacation->setData('status', $data['status']);
                            $vacation->save();
                        }
                    }else{
                        $time = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\Timezone');
                        $vacation->setData('created_at', date('Y-m-d H:i:s',$time->scopeTimeStamp()));
                        $vacation->setData('updated_at', date('Y-m-d H:i:s',$time->scopeTimeStamp()));
                        $vacation->setData('seller_id', $this->_customerSession->getId());
                        $vacation->setData('vacation_message', $data['vacation_message']);
                        $vacation->setData('date_from', $data['date_from']);
                        $vacation->setData('date_to', $data['date_to']);
                        $vacation->setData('text_add_cart', $data['text_add_cart']);
                        $vacation->setData('status', $data['status']);
                        $vacation->save();
                    }
                }
            }  
            $msg = __('You saved successfully.');
            $this->messageManager->addSuccess( $msg );
            $this->_redirect( 'sellervacation/index/view' );  
        }catch (\Exception $e) {    
            $this->messageManager->addError($e->getMessage()); 
            $this->_redirect( 'sellervacation/index/view' );       
        }    
    } 
}