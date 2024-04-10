<?php

namespace Legacy\Converge\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class Data extends AbstractHelper
{
    const PAYMENT_CODE = 'legacy_converge';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @param string $fields
     * @return bool|string
     */
    private function getConfigData(string $fields)
    {
        if (empty($fields)) {
            return false;
        }
        return $this->scopeConfig->getValue(
            'payment/' . $this::PAYMENT_CODE . '/' .$fields,
            ScopeInterface::SCOPE_STORE,
        );
    }

    /**
     * Returns true if mode is == 1
     */
    public function getIsSandboxMode()
    {
        return $this->getConfigData('mode') == '1';
    }

    /**
     * Retrieves the Elavon Account ID
     */
    public function getAccountId()
    {
        return $this->getConfigData('account_id');
    }

    /**
     * Retrieves the Elavon Vendor ID
     */
    public function getVendorId()
    {
        return $this->getConfigData('vendor_id');
    }

    /**
     * Retrieves the Elavon User ID
     */
    public function getUserId()
    {
        return $this->getConfigData('user_id');
    }

    /**
     * Retrieve Elavon PIN
     */
    public function getPin()
    {
        return $this->getConfigData('pin');
    }

    /**
     * Retrieve Sandbox endpoint
     */
    public function getSandboxEndpoint()
    {
        return $this->getConfigData('xml_api_endpoint_demo');
    }

    /**
     * Retrieve Production endpoint
     */
    public function getProductionEndpoint()
    {
        return $this->getConfigData('xml_api_endpoint_production');
    }
}