<?php
namespace Magetop\Messages\Block;
class Lists extends \Magento\Framework\View\Element\Template
{
    protected $_gridFactory; 
    protected $_replyFactory;   
            
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\App\ResourceConnection $resource,
        \Magetop\Messages\Model\MessagesFactory $gridFactory,
        \Magetop\Messages\Model\ReplyFactory $replyFactory,                
		\Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->_gridFactory = $gridFactory;
        $this->_replyFactory = $replyFactory;                
		$this->_customerSession = $customerSession;
		$this->_resource = $resource;
        parent::__construct($context, $data);
    }
    
    /**
	* get list Messages
	* @return $items
	**/
	function getMessages()
	{
	   	$customerSession = $this->_customerSession;
		$sellerid = $customerSession->getId();
	   	$collection = null;
        if($sellerid > 0)
		{
            //get collection of data 
            $collection = $this->_gridFactory->create()->getCollection()->addFieldToFilter('is_active',1);
    		$collection->getSelect()->where('main_table.user_id=?', $this->_customerSession->getId())->orWhere('main_table.usercontact_id=?', $this->_customerSession->getId());
            $collection->setOrder('reply_date', 'DESC' );
            $limit = $this->getRequest()->getParam('limit',5);
			if($limit > 0)
			{
				$collection->setPageSize($limit);
			}
            $curPage = $this->getRequest()->getParam('p',1);
			if($curPage > 1)
			{
				$collection->setCurPage($curPage);
			}
        }
        return $collection;
	}
    
    public function getLastestReply($messages_id)
    {
        $collection = $this->_replyFactory->create()->getCollection()->addFieldToFilter('messages_id',$messages_id);
        $data = $collection->getLastItem();
		
		if ( $data['description'] != '' ) {
			if ( $data['user_id'] == $this->_customerSession->getId() ) { 
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
	
	public function getFromUserId($messages_id)
    {
        $collection = $this->_gridFactory->create()->getCollection()->addFieldToFilter('messages_id',$messages_id);
        $data = $collection->getLastItem();
		if ( $data['user_id'] == $this->_customerSession->getId() ) { 
			$from_user_id = $data['usercontact_id'];
		} else {
			$from_user_id = $data['user_id'];
		}
		 	
        return $from_user_id;
    }
	
	public function getCurentUserId()
    {
		return $this->_customerSession->getId();
	}
  
    protected function _prepareLayout()
    {
        $collection = $this->getMessages();
        parent::_prepareLayout();
        if ($collection) {
            // create pager block for collection
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager','my.custom.pager');
            $pager->setAvailableLimit(array(5=>5,10=>10,20=>20,'30'=>'30')); 
            $pager->setCollection($collection);
            $this->setChild('pager', $pager);
            $collection->load();
        }
        return $this;
    }
  
    /**
     * @return string
     */
    // method for get pager html
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }  

    public function getMessagesInformation()
    {
        return $this->_coreRegistry->registry('messagesData');
    }
}
?>