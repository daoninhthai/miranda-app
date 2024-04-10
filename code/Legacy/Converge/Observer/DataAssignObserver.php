<?php declare(strict_types=1);

namespace Legacy\Converge\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use Psr\Log\LoggerInterface;

class DataAssignObserver extends AbstractDataAssignObserver implements ObserverInterface
{
    protected $logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        try {
            $this->logger->info('DataAssignObserver triggered.');

            // Ensure the observer is not null
            if (empty($observer)) {
                return;
            }

            // Read the payment model argument from the observer
            $data = $observer->getEvent()->getData('data');
            $paymentModel = $observer->getEvent()->getData('payment_model');

            // Retrieve additional data
            $additionalData = $data->getData('additional_data');

            $this->logger->info('additional data below');
            $this->logger->info(json_encode($additionalData));

            // Ensure that data exists and the necessary keys are set
            if (is_array($additionalData)) {
                $paymentModel->setData('additional_data', json_encode($additionalData));
                
                if (isset($additionalData['cc_number'])) {
                    $paymentModel->setData('cc_number_enc', $additionalData['cc_number']);
                }

                if (isset($additionalData['cc_type'])) {
                    $paymentModel->setData('cc_type', $additionalData['cc_type']);
                }

                if (isset($additionalData['cc_exp_month'])) {
                    $paymentModel->setData('cc_exp_month', $additionalData['cc_exp_month']);
                }

                if (isset($additionalData['cc_exp_year'])) {
                    $paymentModel->setData('cc_exp_year', $additionalData['cc_exp_year']);
                }

                if (isset($additionalData['cc_cid'])) {
                    $paymentModel->setData('cc_secure_verify', $additionalData['cc_cid']);
                }
                if (isset($additionalData['cc_cid'])) {
                    $paymentModel->setData('cc_cid', $additionalData['cc_cid']);
                }

                if (isset($additionalData['cc_number'])) {
                    $paymentModel->setData('cc_last_4', substr($additionalData['cc_number'], -4));
                }
            } 
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            throw new \Magento\Framework\Exception\LocalizedException(
                __($e->getMessage())
            );
        }
    }
}
