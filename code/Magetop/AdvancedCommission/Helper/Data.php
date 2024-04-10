<?php
namespace Magetop\AdvancedCommission\Helper;
 
use Magento\Framework\App\Helper\AbstractHelper;
//use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Api\CategoryRepositoryInterface;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    //protected $_scopeConfig;
	
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
		\Magento\Eav\Model\Entity\Attribute\SetFactory $setFactory
        //ScopeConfigInterface $scopeConfig
    ) 
	{
        parent::__construct($context);
        //$this->_scopeConfig = $scopeConfig;
		$this->assetRepo = $assetRepo;
		$this->_storeManager = $storeManager;
		$this->categoryRepository = $categoryRepository;
		$this->_objectmanager = $objectmanager;
		$this->_setFactory = $setFactory;
		$this->_categoryFactory = $categoryFactory;
    }
    
    public function getCommission($product)
    {
        $data = null;
        $commission  = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\AdvancedCommission\Model\AdvancedCommission')
                                                                          ->getCollection()
                                                                          ->setOrder('priority','ASC');
        foreach($commission as $cm){
            if($cm->getIsActive() == '1'){            
                $catalogRule = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\AdvancedCommission\Model\AdvancedCommission')->load($cm->getId());
        		if($catalogRule->validate($product)){        		  
        			$data = $cm->getCommission();
                    break;   
        		}
            } 
        }
        return $data;
    } 
}