<?php

namespace Legacy\Converge\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Invoice;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Magento\Framework\App\State;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Framework\Exception\LocalizedException;
use Legacy\Converge\Helper\Data;
use Psr\Log\LoggerInterface;

class CreateInvoiceObserver implements ObserverInterface
{
    protected $invoiceService;
    protected $orderRepository;
    protected $invoiceRepository;
    protected $invoiceSender;
    protected $transaction;
    protected $logger;
    protected $state;
    public $helper;

    /**
     * Constructor
     */
    public function __construct(
        Data $helper,
        InvoiceService $invoiceService,
        OrderRepositoryInterface $orderRepository,
        InvoiceRepositoryInterface $invoiceRepository,
        InvoiceSender $invoiceSender,
        State $state,
        LoggerInterface $logger,
        Transaction $transaction
    ) {
        $this->helper = $helper;
        $this->invoiceService = $invoiceService;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->invoiceSender = $invoiceSender;
        $this->state = $state;
        $this->logger = $logger;
        $this->transaction = $transaction;
    }

    /**
     * Execute observer to create invoice
     */
    public function execute(Observer $observer)
    {
        // config api
        $this->logger->info('Create invoice trigger');
        $sandboxMode = $this->helper->getIsSandboxMode();
        $accountId = $this->helper->getAccountId();
        $vendorId = $this->helper->getVendorId();
        $userId = $this->helper->getUserId();
        $pin = $this->helper->getPin();
        $sandboxEndpoint = $this->helper->getSandboxEndpoint();
        $productionEndpoint = $this->helper->getProductionEndpoint();

        // Get order from event
        $order = $observer->getEvent()->getOrder();
        $payment = $observer->getEvent()->getOrder()->getPayment();

        $postCode = $order->getBillingAddress()->getPostcode();
        $city = $order->getBillingAddress()->getCity();
        $street = implode(' ', $order->getBillingAddress()->getStreet());

        // card deets
        $cardExpYear = $payment->getData('cc_exp_year');
        $cardExpMonth = $payment->getData('cc_exp_month');
        $formattedExpDate = sprintf('%02d%s', $cardExpMonth, substr($cardExpYear, -2));
        $cardNumber = $payment->getData('cc_number_enc');
        $ccCid = $payment->getData('cc_cid');
                
        if (!$order->canInvoice()) {
            return; 
        }
        
        // Make sure the order is valid
        $this->logger->info('$order->getState() ' .$order->getState()); 
        if ($order && $order->getState() == Order::STATE_NEW) {
            try {
                // pwedi napo siguro to delete sir dahil processing napo yun state nya?
                $order->setState(Order::STATE_PROCESSING);
                $order->setStatus(Order::STATE_PROCESSING);
                $this->orderRepository->save($order);

                // Create the invoice
                $invoice = $this->invoiceService->prepareInvoice($order);
                if (!$invoice) {
                    throw new LocalizedException(__('We can\'t create an invoice for this order.'));
                }

                // Check if the invoice can be created
                if (!$invoice->getTotalQty()) {
                    throw new LocalizedException(__('Cannot create an invoice without items.'));
                }

                // Register and save the invoice
                $invoice->register();
                $this->invoiceRepository->save($invoice);

                // Update order status
                $transactionSave = $this->transaction->addObject($invoice)->addObject($invoice->getOrder());
                $transactionSave->save();

                $order->addCommentToStatusHistory(
                    __('Notified customer about invoice creation #%1.', $invoice->getId())
                )->setIsCustomerNotified(true)->save(); 

                // invoice details
                $invoiceNumber = $invoice->getIncrementId();
                $invoiceAmount = $invoice->getGrandTotal();

                $this->logger->info('trigger request to elavon');
                // send request
             
                $xmlData = <<<XML
                <txn>
                    <ssl_transaction_type>ccsale</ssl_transaction_type>
                    <ssl_account_id>$accountId</ssl_account_id>
                    <ssl_user_id>$userId</ssl_user_id>
                    <ssl_pin>$pin</ssl_pin>
                    <ssl_vendor_id>$vendorId</ssl_vendor_id>
                    <ssl_card_number>$cardNumber</ssl_card_number>
                    <ssl_exp_date>$formattedExpDate</ssl_exp_date>
                    <ssl_cvv2cvc2_indicator>9</ssl_cvv2cvc2_indicator>
                    <ssl_cvv2cvc2>$ccCid</ssl_cvv2cvc2>
                    <ssl_amount>$invoiceAmount</ssl_amount>
                    <ssl_avs_address>$street</ssl_avs_address>
                    <ssl_avs_zip>$postCode</ssl_avs_zip>
                    <ssl_partial_auth_indicator>1</ssl_partial_auth_indicator>
                    <ssl_invoice_number>$invoiceNumber</ssl_invoice_number>
                </txn>
                XML;
                
                $urlHost = $sandboxMode ? $sandboxEndpoint : $productionEndpoint;
                $client = new \GuzzleHttp\Client();
                $response = $client->request('POST', $urlHost, [
                    'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
                    'form_params' => ['xmldata' => $xmlData],
                ]);
            
                $this->logger->info('Response: ' . $response->getBody()->getContents());
                $this->invoiceSender->send($invoice);
                $this->orderRepository->save($order);
            } catch (\Exception $e) {
                \Magento\Framework\App\ObjectManager::getInstance()
                    ->get(\Psr\Log\LoggerInterface::class)
                    ->critical($e->getMessage());
            }
        }
    }
}
