<?php
/**
 * Copyright © 2020 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magetop\SellerMassImportExport\Controller\Index;

use Magetop\SellerMassImportExport\Controller\Index\ImportResult as ImportResultController;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Block\Adminhtml\Import\Frame\Result as ImportResultBlock;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\ImportExport\Model\Import\Adapter as ImportAdapter;

class Validate extends ImportResultController
{
    /**
     * @var Import
     */
    private $import;

    /**
     * Validate uploaded files action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $message = '';
        if ($data) {
            /** @var $import \Magento\ImportExport\Model\Import */
            $import = $this->getImport()->setData($data);
            try {
                $source = ImportAdapter::findAdapterFor(
                    $import->uploadSource(),
                    $this->_objectManager->create('Magento\Framework\Filesystem')->getDirectoryWrite(DirectoryList::ROOT),
                    $data[$import::FIELD_FIELD_SEPARATOR]
                );
                $importValidate = $this->processValidationResult($import->validateSource($source));
                $success = $importValidate['success'];
                $message .= $importValidate['message'];
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $success = false;
                $message .= $e->getMessage();
            } catch (\Exception $e) {
                $success = false;
                $message .= __('Sorry, but the data is invalid or the file is not uploaded.');
            }
        } elseif ($this->getRequest()->isPost() && empty($_FILES)) {
            $success = false;
            $message .= __('The file was not uploaded.');
        }
        $response = array('message' => $message, 'success' => $success);
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($response);
        return $resultJson;
    }

    /**
     * @param bool $validationResult
     * @param ImportResultBlock $resultBlock
     * @return void
     */
    private function processValidationResult($validationResult)
    {
        $message = '';
        $import = $this->getImport();
        if (!$import->getProcessedRowsCount()) {
            if (!$import->getErrorAggregator()->getErrorsCount()) {
                $success = false;
                $message .= __('This file is empty. Please try another one.');
            } else {
                foreach ($import->getErrorAggregator()->getAllErrors() as $error) {
                    $success = false;
                    $message .= $error->getErrorMessage();
                }
            }
        } else {
            $errorAggregator = $import->getErrorAggregator();
            if (!$validationResult) {
                $success = false;
                $message .= __('Data validation failed. Please fix the following errors and upload the file again.').'mst';
                $message .= $this->addErrorMessages($errorAggregator);
            } else {
                if ($import->isImportAllowed()) {
                    $success = true;
                    $message .= __('File is valid! To start import process press "Import" button');
                } else {
                    $success = false;
                    $message .= _('The file is valid, but we can\'t import it for some reason.');
                }
            }
            $message .= __(
                    'Checked rows: %1, checked entities: %2, invalid rows: %3, total errors: %4',
                    $import->getProcessedRowsCount(),
                    $import->getProcessedEntitiesCount(),
                    $errorAggregator->getInvalidRowsCount(),
                    $errorAggregator->getErrorsCount()
                );
        }
        return array('message' => $message, 'success' => $success);
    }

    /**
     * @return Import
     * @deprecated
     */
    private function getImport()
    {
        if (!$this->import) {
            $this->import = $this->_objectManager->get(Import::class);
        }
        return $this->import;
    }
}
