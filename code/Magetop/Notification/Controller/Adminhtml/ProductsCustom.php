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

            foreach($ids as $id) {
                $sellerProductModel = $this->_objectManager->create($this->_modelClass)
                    ->load($id)
                    ->setData($this->_statusField, $status)
                    ->save();
                $product = $objectManager->create('\Magento\Catalog\Api\ProductRepositoryInterface')->getById($sellerProductModel->getProductId());
                $product->setStatus($status);
                $objectManager->create('\Magento\Catalog\Api\ProductRepositoryInterface')->save($product);

                $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                $connection = $resource->getConnection();
                $tableName = $resource->getTableName('multivendor_product'); //gives table name with prefix
                $sql = "Select * FROM " . $tableName . " WHERE product_id = " . $sellerProductModel->getProductId();
                $result = $connection->fetchAll($sql); // gives associated array, table fields as
                if($status == 1){
                    $pStatus = __('Approved');
                }else{
                    $pStatus = __('Unapproved');
                }
                $this->_objectManager->create('Magetop\Marketplace\Helper\EmailSeller')->sendProductSellerEmail($product->getName(), $result[0]['user_id'], $pStatus);
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
