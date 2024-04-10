<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Multiple_Table_Rate_Shipping
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerTableRateShipping\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
class Version extends \Magento\Config\Block\System\Config\Form\Field
{
	const EXTENSION_URL = 'https://www.magetop.com/magento-multi-vendor-marketplace-extension';

	/**
	 * @var \Magetop\SellerTableRateShipping\Helper\Data $helper
	 */
	protected $_helper;

	/**
	 * @param \Magento\Backend\Block\Template\Context $context
	 * @param \Magetop\SellerTableRateShipping\Helper\Data $helper
	 */
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magetop\SellerTableRateShipping\Helper\Data $helper
	) {
		$this->_helper = $helper;
		parent::__construct($context);
	}

	/**
	 * @param AbstractElement $element
	 * @return string
	 */
	protected function _getElementHtml(AbstractElement $element)
	{
		$extensionVersion   = $this->_helper->getExtensionVersion();
		$versionLabel       = sprintf('<a href="%s" title="Seller Table Rate Shipping" target="_blank">%s</a>', self::EXTENSION_URL, $extensionVersion);
		$element->setValue($versionLabel);
		return $element->getValue();
	}
}