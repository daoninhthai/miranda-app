<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Vacation
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerVacation\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	const XML_PATH_ENABLED          = 'magetop_sellervacation/general/enabled';
	const XML_PATH_DEBUG            = 'magetop_sellervacation/general/debug';

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
    protected $_sellervacation;

	/**
	 * @param \Magento\Framework\App\Helper\Context $context
	 * @param \Magento\Framework\Module\ModuleListInterface $moduleList
	 */
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magetop\Marketplace\Model\ProductsFactory $mkProduct,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magetop\SellerVacation\Model\SellerVacationFactory $sellervacationFactory
	) {
		$this->_logger = $context->getLogger();
		$this->_moduleList = $moduleList;
        $this->_mkProduct = $mkProduct;
        $this->_resource = $resource;
        $this->_sellervacation = $sellervacationFactory;
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
		$moduleCode = 'Magetop_SellerVacation';
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
    
    public function getVacationByProductId($product_id){
        $data = null;
        $mkProductData = $this->_mkProduct->create()->getCollection()
                        ->addFieldToFilter('product_id',$product_id)
                        ->addFieldToFilter('status',1);
                        
        //Kien 19/5/2020 - update filter seller approve        
        $tableMKuser = $this->_resource->getTableName('multivendor_user');
        $mkProductData->getSelect()->joinLeft(array('mk_user'=>$tableMKuser),'main_table.user_id = mk_user.user_id',array())
            ->where('mk_user.userstatus = 1');
            
        $mkProductData = $mkProductData->getFirstItem();
    	if($mkProductData && $mkProductData->getId())
		{
			$useId = $mkProductData->getUserId();
            $data = $this->_sellervacation->create()->getCollection()
                                                    ->addFieldToFilter('seller_id',$useId)
                                                    ->addFieldToFilter('date_from',array('lteq'=>date('Y-m-d')))
                                                    ->addFieldToFilter('date_to',array('gteq'=>date('Y-m-d')))
                                                    ->addFieldToFilter('status',1)
                                                    ->getFirstItem();
		}
        return $data;
    }
    
    public function getVacationBySellerId($sellerId){
        $data = null;
        $data = $this->_sellervacation->create()->getCollection()
                                                ->addFieldToFilter('seller_id',$sellerId)
                                                ->addFieldToFilter('date_from',array('lteq'=>date('Y-m-d')))
                                                ->addFieldToFilter('date_to',array('gteq'=>date('Y-m-d')))
                                                ->addFieldToFilter('status',1) 
                                                ->getFirstItem();
        return $data;
    }
}