<?php
namespace Magetop\Notification\Controller\Adminhtml;
class ProductsCustom extends \Magetop\Marketplace\Controller\Adminhtml\Products\MassStatus
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
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $status = $this->getRequest()->getParam('status');
            $statusFieldName = $this->_statusField;

            if (is_null($status)) {
                throw new \Exception(__('Parameter "Status" missing in request data.'));
            }

            if (is_null($statusFieldName)) {
                throw new \Exception(__('Status Field Name is not specified.'));
            }

            foreach ($ids as $id) {
                $sellerProductModel = $this->_objectManager->create($this->_modelClass)
                    ->load($id)
                    ->setData($this->_statusField, $status)
                    ->save();

                $product = $objectManager->create('\Magento\Catalog\Api\ProductRepositoryInterface')
                    ->getById($sellerProductModel->getProductId());
                $product->setStatus($status);
                $objectManager->create('\Magento\Catalog\Api\ProductRepositoryInterface')->save($product);

                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();
                $tableName = $resource->getTableName('multivendor_product');
                $sql = "SELECT * FROM " . $tableName . " WHERE product_id = " . $sellerProductModel->getProductId();
                $result = $connection->fetchAll($sql);

                if ($status == 1) {
                    $pStatus = __('Approved');
                    $notificationType = 1; // Type for approved products
                } else {
                    $pStatus = __('Unapproved');
                    $notificationType = 2; // Type for unapproved products
                }

                // Send email
                $this->_objectManager->create('Magetop\Marketplace\Helper\EmailSeller')
                    ->sendProductSellerEmail($product->getName(), $result[0]['user_id'], $pStatus);

                // Add notification record
                $notifyModel = $this->_objectManager->create('Magetop\Notification\Model\Notification');
                $notificationContent = __('Your product "%1" has been %2', $product->getName(), strtolower($pStatus));

                $notificationData = [
                    'seller_id' => $result[0]['user_id'], // Get seller ID from multivendor_product table
                    'type' => $notificationType, // 1 for approved, 2 for unapproved
                    'content' => $notificationContent,
                    'status' => 0, // 0 for unread, 1 for read
                    'created_at' => date('Y-m-d H:i:s') // Current timestamp
                ];

                $notifyModel->setData($notificationData);
                $notifyModel->save();
//                $connection->insert(
//                    $resource->getTableName('multivendor_notification'),
//                    $notificationData
//                );
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
