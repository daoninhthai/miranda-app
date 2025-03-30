<?php
namespace Magetop\Notification\Controller\Adminhtml;
class SellerCustom extends \Magetop\Marketplace\Controller\Adminhtml\Sellers\MassStatus
{
    protected function _massStatusAction()
    {
        $ids = $this->getRequest()->getParam($this->_idKey);

        if (!is_array($ids)) {
            $ids = [$ids];
        }
        $model = $this->_getModel(false);
        $error = false;
        try {
            $status = $this->getRequest()->getParam('status');
            $statusFieldName = $this->_statusField;
            if (is_null($status)) {
                throw new \Exception(__('Parameter "Status" missing in request data.'));
            }
            if (is_null($statusFieldName)) {
                throw new \Exception(__('Status Field Name is not specified.'));
            }
            foreach($ids as $id) {
                $data = $this->_objectManager->create($this->_modelClass)
                    ->load($id)
                    ->setData($this->_statusField, $status)
                    ->save();
                if($status == 1){
                    $sStatus = __('Approved');
                    $notificationType = 1; // Type for approved seller
                    $this->_objectManager->create('Magetop\Marketplace\Helper\EmailSeller')->sendApproveSellerEmail($data->getUserId());
                }else{
                    $sStatus = __('Disapproved');
                    $notificationType = 2; // Type for unapproved seller
                    $this->_objectManager->create('Magetop\Marketplace\Helper\EmailSeller')->sendUnapproveSellerEmail($data->getUserId());
                }

                // Add notification record
                $notifyModel = $this->_objectManager->create('Magetop\Notification\Model\Notification');
                $notificationContent = __('You have been %1 as a seller.', strtolower($sStatus));

                $notificationData = [
                    'seller_id' => $data->getUserId(), // Get seller ID from multivendor_product table
                    'type' => $notificationType, // 1 for approved, 2 for unapproved
                    'content' => $notificationContent,
                    'status' => 0, // 0 for unread, 1 for read
                    'created_at' => date('Y-m-d H:i:s') // Current timestamp
                ];

                $notifyModel->setData($notificationData);
                $notifyModel->save();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $error = true;
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $error = true;
            $this->messageManager->addException($e, __('We can\'t change status of '.strtolower($model->getOwnTitle()).' right now. '.$e->getMessage()));
        }
        if (!$error) {
            $this->messageManager->addSuccess(
                __($model->getOwnTitle(count($ids) > 1).' status have been changed.')
            );
        }
        $this->_redirect('*/*');
    }
}
