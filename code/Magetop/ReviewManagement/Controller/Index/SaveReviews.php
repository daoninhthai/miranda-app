<?php
/**
* @author      Magetop Developer (Kien)
* @package     Magento Multi Vendor Marketplace_Review_Management
* @copyright   Copyright (c) Magetop (https://www.magetop.com)
* @terms       https://www.magetop.com/terms
* @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
**/

namespace Magetop\ReviewManagement\Controller\Index;
 
use Magento\Framework\App\Action\Context;

class SaveReviews extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        Context $context
    )
    {
        parent::__construct($context);
    }
 
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        $reviewsOption = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\ReviewManagement\Model\ReviewManagement');
        $customer = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Customer\Model\Session');
        $time = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\Timezone');
        $reviewsOption->setReviewId($data['review_id']);
        $reviewsOption->setCustomerId($customer->getId());
        $reviewsOption->setType($data['type']);
        $reviewsOption->setContent($data['content']);
        $reviewsOption->setPostBy($customer->getCustomer()->getName());
        $reviewsOption->setCreatedAt(date('Y-m-d H:i:s',$time->scopeTimeStamp()));
        $reviewsOption->setStatus(1);
        if($data['type'] == 'comment'){
            $reviewsOption->save();
            $result['content'] = '<li style="border-bottom: 1px dashed silver;">
                                    <span class="text">'.$reviewsOption->getContent().'</span><br />
                                    <span class="dateandtime">Posted '.$reviewsOption->getCreatedAt().' by <i class="name">'.$reviewsOption->getPostBy().'</i></span>
                                </li>';
            $result['count'] = 1;
        }else{
            $reviewsOptionValidate = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\ReviewManagement\Model\ReviewManagement')
                                                                                        ->getCollection()
                                                                                        ->addFieldToFilter('review_id',$data['review_id'])
                                                                                        ->addFieldToFilter('customer_id',$customer->getId())
                                                                                        ->addFieldToFilter('type',$data['type'])
                                                                                        ->getFirstItem();
            if(!$reviewsOptionValidate->getId()){
                $reviewsOption->save();
                $result['count'] = 1;
            }else{
                $result['count'] = 0;
            }
        }
        $this->getResponse()->representJson($this->_objectManager->get('Magento\Framework\Json\Helper\Data')->jsonEncode($result));
    }
}
 