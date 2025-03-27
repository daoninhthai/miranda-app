<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Review_Management
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\ReviewManagement\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	const XML_PATH_ENABLED          = 'marketplace/general/seller_approval';
	const XML_PATH_DEBUG            = 'marketplace/general/product_approval';

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
    protected $_reviewmanagement;
    protected $_objectmanager;

	/**
	 * @param \Magento\Framework\App\Helper\Context $context
	 * @param \Magento\Framework\Module\ModuleListInterface $moduleList
	 */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magetop\Marketplace\Model\ProductsFactory $mkProduct,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magetop\ReviewManagement\Model\ReviewManagementFactory $reviewmanagementFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager
	) {
		$this->_logger = $context->getLogger();
		$this->_moduleList = $moduleList;
        $this->_mkProduct = $mkProduct;
        $this->_resource = $resource;
        $this->_reviewmanagement = $reviewmanagementFactory;
        $this->_objectmanager = $objectmanager;
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
	public function getSellerReviewBug($_proId)
	{
		$_sellerReviewBug = true;
		if($this->isEnabled() && $this->getDebugStatus() && !$this->checkPurchasedProduct($_proId)){
			$_sellerReviewBug = false;
		}
		return $_sellerReviewBug;
	}

	public function getExtensionVersion()
	{
		$moduleCode = 'Magetop_ReviewManagement';
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
    
    public function checkPurchasedProduct($product_id){
        $flag = false;
        $customerSession = $this->_objectmanager->create('Magento\Customer\Model\Session');
		$_dataObject = $customerSession->getCustomerData();
        if(is_object($_dataObject)){
            $coreOrderModel = $this->_objectmanager->create('Magento\Sales\Model\Order')
                                                    ->getCollection()
                                                    ->addAttributeToFilter('customer_id',$_dataObject->getId());
            if(count($coreOrderModel) > 0){
                foreach($coreOrderModel as $order){
                    $orderItems = $order->getAllItems();
                    foreach($orderItems as $od){
                        if($product_id == $od->getProductId()){
                            $flag = true;
                            break;
                        }
                    }
                }
            }
        }
        return $flag;
    }
    
    public function checkLogin(){
        $flag = false;
        $customerSession = $this->_objectmanager->create('Magento\Customer\Model\Session');
		if($customerSession->isLoggedIn()) {
           $flag = true;
        }
        return $flag;
    } 
}