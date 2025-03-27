<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Mass_Import_Export
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerMassImportExport\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	const XML_PATH_ENABLED          = 'magetop_sellermassimportexport/general/enabled';
	const XML_PATH_DEBUG            = 'magetop_sellermassimportexport/general/debug';

	/**
	 * @var \Psr\Log\LoggerInterface
	 */
	protected $_logger;

	/**
	 * @var \Magento\Framework\Module\ModuleListInterface
	 */
	protected $_moduleList;
    protected $_mkProduct;
    protected $_resource;
    protected $_sellermassimportexport;

	/**
	 * @param \Magento\Framework\App\Helper\Context $context
	 * @param \Magento\Framework\Module\ModuleListInterface $moduleList
	 */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magetop\Marketplace\Model\ProductsFactory $mkProduct,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magetop\SellerMassImportExport\Model\SellerMassImportExportFactory $sellermassimportexportFactory
	) {
		$this->_logger = $context->getLogger();
		$this->_moduleList = $moduleList;
        $this->_mkProduct = $mkProduct;
        $this->_resource = $resource;
        $this->_sellermassimportexport = $sellermassimportexportFactory;
		parent::__construct($context);
	}

	/**
	 * Check if enabled
	 *
	 * @return string|null
	 */
	public function isEnabled()
	{
		return $this->scopeConfig->getValue(
			self::XML_PATH_ENABLED,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	public function getDebugStatus()
	{
		return $this->scopeConfig->getValue(
			self::XML_PATH_DEBUG,
			\Magento\Store\Model\ScopeInterface::SCOPE_STORE
		);
	}

	public function getExtensionVersion()
	{
		$moduleCode = 'Magetop_SellerMassImportExport';
		$moduleInfo = $this->_moduleList->getOne($moduleCode);
		return $moduleInfo['setup_version'];
	}

	/**
	 *
	 * @param $message
	 * @param bool|false $useSeparator
	 */
	public function log($message, $useSeparator = false)
	{
		if ($this->getDebugStatus()) {
			if ($useSeparator) {
				$this->_logger->addDebug(str_repeat('=', 100));
			}
			$this->_logger->addDebug($message);
		}
	}
}