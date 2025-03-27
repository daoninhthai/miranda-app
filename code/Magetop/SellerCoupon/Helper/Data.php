<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Coupon
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerCoupon\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class Data extends AbstractHelper
{
   /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
	
	protected $_objectmanager;
	
	protected $assetRepo;
	
	protected $categoryRepository;
	
	protected $_storeManager;
	
	protected $_categoryFactory;
	
	protected $_category;
	
	protected $_setFactory;
 
    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
		ObjectManagerInterface $objectmanager,
		\Magento\Framework\View\Asset\Repository $assetRepo,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Catalog\Model\CategoryFactory $categoryFactory,
		CategoryRepositoryInterface $categoryRepository,
		\Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory,
        ScopeConfigInterface $scopeConfig
    ) 
	{
        parent::__construct($context);
        $this->_scopeConfig = $scopeConfig;
		$this->assetRepo = $assetRepo;
		$this->_storeManager = $storeManager;
		$this->categoryRepository = $categoryRepository;
		$this->_objectmanager = $objectmanager;
		$this->_setFactory = $setFactory;
		$this->_categoryFactory = $categoryFactory;
    }
    
    public function getCoupon($product)
    {
        $data = null;
        $coupon  = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\SellerCoupon\Model\SellerCoupon')
                                                                      ->getCollection()
                                                                      ->setOrder('priority','ASC');
        foreach($coupon as $cm){
            if($cm->getIsActive() == '1'){            
                $catalogRule = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\SellerCoupon\Model\SellerCoupon')->load($cm->getId());
        		if($catalogRule->validate($product)){        		  
        			$data = $cm->getCoupon();
        		}
                break;    
            } 
        }
        return $data;
    } 
}