<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Multiple_Flat_Rate_Shipping
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerStorePickup\Model\Carrier;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Config;
use Magento\Shipping\Model\Rate\ResultFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory;
use Magento\Quote\Model\Quote\Address\RateResult\Method;
use Magento\Quote\Model\Quote\Address\RateResult\MethodFactory;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Psr\Log\LoggerInterface;

class SellerStorePickup extends AbstractCarrier implements CarrierInterface
{
	/**
	 * Carrier's code
	 *
	 * @var string
	 */
	protected $_code = 'sellerstorepickup';

	/**
	 * Whether this carrier has fixed rates calculation
	 *
	 * @var bool
	 */
	protected $_isFixed = true;

	/**
	 * @var ResultFactory
	 */
	protected $rateResultFactory;

	/**
	 * @var MethodFactory
	 */
	protected $rateMethodFactory;

	/**
	 * @param ScopeConfigInterface $scopeConfig
	 * @param ErrorFactory $rateErrorFactory
	 * @param LoggerInterface $logger
	 * @param ResultFactory $rateResultFactory
	 * @param MethodFactory $rateMethodFactory
	 * @param array $data
	 */
	public function __construct(
		ScopeConfigInterface $scopeConfig,
		ErrorFactory $rateErrorFactory,
		LoggerInterface $logger,
		ResultFactory $rateResultFactory,
		MethodFactory $rateMethodFactory,
		array $data = []
	) {
		$this->rateResultFactory = $rateResultFactory;
		$this->rateMethodFactory = $rateMethodFactory;
		parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
	}

	/**
	 * Generates list of allowed carrier`s shipping methods
	 * Displays on cart price rules page
	 *
	 * @return array
	 * @api
	 */
	public function getAllowedMethods()
	{
		return [$this->getCarrierCode() => __($this->getConfigData('name'))];
	}

	/**
	 * Collect and get rates for storefront
	 *
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
	 * @param RateRequest $request
	 * @return DataObject|bool|null
	 * @api
	 */
	public function collectRates(RateRequest $request)
	{
		/**
		 * Make sure that Shipping method is enabled
		 */
		if (!$this->isActive()) {
			return false;
		}

		/** @var \Magento\Shipping\Model\Rate\Result $result */
		$result = $this->rateResultFactory->create();
        
        $SellerStorePickupPrice =  \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Catalog\Model\Session')->getSellerStorePickupShippingPrice();
		$shippingPrice = $SellerStorePickupPrice?$SellerStorePickupPrice:$this->getConfigData('price');
		$method = $this->rateMethodFactory->create();

		/**
		 * Set carrier's method data
		 */
		$method->setCarrier($this->getCarrierCode());
		$method->setCarrierTitle($this->getConfigData('title'));

		/**
		 * Displayed as shipping method under Carrier
		 */
		$method->setMethod($this->getCarrierCode());
		$method->setMethodTitle($this->getConfigData('name'));

		$method->setPrice($shippingPrice);
		$method->setCost($shippingPrice);

		$result->append($method);
		return $result;
	}
}