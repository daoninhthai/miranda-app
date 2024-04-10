<?php
/**
 * @author      Magetop
 * @package     Magetop_Api
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Api\Controller\Marketplace;

use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Magetop\Api\Helper\Data as DataHelper;

class GetSellerMessages extends \Magetop\Api\Controller\AbstractController
{
    protected $_gridFactory; 
    protected $_replyFactory; 
    protected $_resource;
	protected $_product;
	protected $_modelSession;
	protected $_summaryFactory;
    
    public function __construct(
        \Magetop\Messages\Model\MessagesFactory $gridFactory,
        \Magetop\Messages\Model\ReplyFactory $replyFactory,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Catalog\Model\Product $product,
		\Magento\Customer\Model\Session $modelSession,
		\Magento\Review\Model\Review\SummaryFactory $summaryFactory,
        Context $context,
        EventManager $eventManager,
        AppEmulation $appEmulation,
        DataHelper $dataHelper
    ) {
        $this->_gridFactory = $gridFactory;
        $this->_replyFactory = $replyFactory;
		$this->_resource = $resource;
		$this->_product = $product;
		$this->_modelSession = $modelSession;
		$this->_summaryFactory = $summaryFactory;
        parent::__construct($context, $eventManager, $appEmulation, $dataHelper);
    }
    
    /**
     * execute category list.
     *
     * @return \Magento\Framework\Controller\ResultFactory::TYPE_JSON
     */
    public function execute(){
        parent::execute();

        $responseData = [];
        $status = true;
        $message = 'Successfully!';
        $data = [];

        try{
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $customerSession = $objectManager->get('Magento\Customer\Model\Session');
            if($customerSession->isLoggedIn()) {
                $customerId = $customerSession->getCustomer()->getId();
            }
            //get collection of data 
            $collection = $this->_gridFactory->create()->getCollection()->addFieldToFilter('is_active',1);
    		$collection->getSelect()->where('main_table.user_id=?', $customerId)->orWhere('main_table.usercontact_id=?', $customerId);
            $collection->setOrder('reply_date', 'DESC' );
            $messagesData = array();
            if ($collection->count()) {
                foreach($collection as $item){
                    $createdat = date('M-d',strtotime( $item->getCreatedAt() ));
                    $message_createdat = str_replace('-',', ',preg_replace('/-/',' ',$createdat,1));	
                    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                    $from_user_id = $this->getFromUserId( $item->getMessagesId() , $customerId );
					$customer = $objectManager->create('Magento\Customer\Model\Customer')->load(  $from_user_id );
                    $customer_name = $customer->getData('firstname').' '.$customer->getData('lastname');
					$status = json_decode(  $item->getStatus(), true);
                    if (  $item->getProductId() > 0 or $item->getOrderId() > 0 ) {
                        if (  $item->getProductId() > 0 ) { 
							$product_detail = $objectManager->create('Magento\Catalog\Model\Product')->load(  $item->getProductId() );
							$content = __('Product Name:').' <a href="'.$product_detail->getProductUrl().'">'.$product_detail->getName().'</a>';
						} else {
							$order_detail = $objectManager->create('Magento\Sales\Model\Order')->load(  $item->getOrderId() );
							$url_order = $this->getUrl('').'sales/order/view/order_id/'.$item->getOrderId();
							$mkHelper =  $this->helper('Magetop\Marketplace\Helper\MkSales');
							$is_seller = $mkHelper->checkSellerOrder( $item->getOrderId() , $customerId );
							if ( $is_seller > 0 ) { 
								$url_order = $this->getUrl('').'marketplace/seller/vieworder/order_id/'.$item->getOrderId();
							}
							$content = __('Order Id:').' <a href="'.$url_order.'">'.$order_detail->getIncrementId().'</a>';
						}
                    }
                    $messagesData[] = array(
                        'message-id' => $item->getMessagesId(),
                        'message-date' => $createdat,
                        'message-sender' => $customer_name,
                        'message-subject' => $item->getTitle(),
                        'message-last-message' => $this->getLastestReply($item->getMessagesId(),$customerId),
                    );
                }
            }
            $data = array(
                'messages' => $messagesData
            );
        }catch(\Exception $e){
            $status = false;
            $message = $e->getMessage();
        }
        $responseData = $this->getResponseData($status, $message, $data);
        
        return $this->returnResultJson($responseData);
    }
    
    public function getLastestReply($messages_id,$customerId)
    {
        $collection = $this->_replyFactory->create()->getCollection()->addFieldToFilter('messages_id',$messages_id);
        $data = $collection->getLastItem();
		
		if ( $data['description'] != '' ) {
			if ($data['user_id'] == $customerId) { 
				$last_message = __('Me:').' '.$data['description'];
			} else {
				$last_message = $data['description'];
			}
		} else { 
			$collection = $this->_gridFactory->create()->getCollection()->addFieldToFilter('messages_id',$messages_id);
			$data = $collection->getLastItem();
			$last_message = $data['description'];
		}
        return $last_message;
    }
	
	public function getFromUserId($messages_id,$customerId)
    {
        $collection = $this->_gridFactory->create()->getCollection()->addFieldToFilter('messages_id',$messages_id);
        $data = $collection->getLastItem();
		if ($data['user_id'] == $customerId) { 
			$from_user_id = $data['usercontact_id'];
		} else {
			$from_user_id = $data['user_id'];
		}
		 	
        return $from_user_id;
    }
}