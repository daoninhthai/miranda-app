<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Controller\Index;
 
use Magento\Framework\App\Action\Context;

class Pay extends \Magento\Framework\App\Action\Action
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
        if($data['type'] == 'complete'){
            $sellerId = $data['seller_id']; 
            $tranId = $data['tran_id']; 
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $transaction = $objectManager->create('Magetop\Marketplace\Model\Transactions')->load($tranId);
            
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $partnerOldModel = $objectManager->create('Magetop\Marketplace\Model\Partner')->getCollection()->addFieldToFilter('sellerid',$sellerId)->getFirstItem();
            
            $partnerNewModel = $objectManager->create('Magetop\Marketplace\Model\Partner')->load($partnerOldModel['id']);
            $partnerNewModel->setAmountreceived($partnerOldModel['amountreceived']+$transaction->getTransactionAmount());
            $partnerNewModel->setAmountpaid($transaction->getTransactionAmount());
            $partnerNewModel->setAmountremain($partnerOldModel['amountremain'] - $transaction->getTransactionAmount());
            $partnerNewModel->save();
            
            $transaction->setPaidStatus(2);
            $transaction->setAdminComment($data['note']);
            $transaction->save();
            $this->messageManager->addSuccess(__('The transaction has been completed'));
            //send mail
            $data['seller_id'] = $sellerId;  
            $data['payment_id'] = $transaction->getPaymentId();
            $data['payment_email'] = $transaction->getPaymentEmail();
            $data['payment_additional'] = $transaction->getPaymentAdditional();
            $data['transaction_amount'] = $transaction->getTransactionAmount();
            $data['amount_paid'] = $transaction->getAmountPaid();
            $data['amount_fee'] = $transaction->getAmountFee();
            $data['status'] = 'Completed';    
                             
            $this->_objectManager->create('Magetop\Marketplace\Helper\EmailSeller')->sendCompleteWithdrawEmailToSeller($data);
            $this->_objectManager->create('Magetop\Marketplace\Helper\EmailSeller')->sendCompelteWithdrawEmailToAdmin($data);  
        }else{
            $sellerId = $data['seller_id']; 
            $tranId = $data['tran_id']; 
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $transaction = $objectManager->create('Magetop\Marketplace\Model\Transactions')->load($tranId);
            
            $transaction->setPaidStatus(3);
            $transaction->setAdminComment($data['note']);
            $transaction->save();
            $this->messageManager->addSuccess(__('The transaction has been canceled'));
            //send mail
            $data['seller_id'] = $sellerId;  
            $data['payment_id'] = $transaction->getPaymentId();
            $data['payment_email'] = $transaction->getPaymentEmail();
            $data['payment_additional'] = $transaction->getPaymentAdditional();
            $data['transaction_amount'] = $transaction->getTransactionAmount();
            $data['amount_paid'] = $transaction->getAmountPaid();
            $data['amount_fee'] = $transaction->getAmountFee(); 
            $data['status'] = 'Canceled';    
                             
            $this->_objectManager->create('Magetop\Marketplace\Helper\EmailSeller')->sendCompleteWithdrawEmailToSeller($data);
            $this->_objectManager->create('Magetop\Marketplace\Helper\EmailSeller')->sendCompelteWithdrawEmailToAdmin($data);  
        }
        $oldUrl = $data['old_url'];
        $this->_redirect($oldUrl);
    }
}
 