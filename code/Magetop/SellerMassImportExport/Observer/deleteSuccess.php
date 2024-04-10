<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Mass_Import_Export
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerMassImportExport\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\ObjectManagerInterface;
use \Magento\Framework\App\RequestInterface;

class deleteSuccess implements ObserverInterface
{   
    protected $_mkProduct;
    protected $_timezone;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    protected $_sessioncustomer;
    protected $_transportBuilder;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;  
	protected $_customerCollectionFactory;
    protected $_request;
    protected $_objectManager;

    /**
     * OrderPlaceAfter constructor.
     *     
     * @param \Magento\Framework\Mail\Template\TransportBuilder   $transportBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface  $scopeConfig
     * @param \Magento\Framework\Translate\Inline\StateInterface  $inlineTranslation          
     * @param array                                               $data
     */
    public function __construct(   
        \Magetop\Marketplace\Model\ProductsFactory $mkProduct,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        \Magento\Store\Model\StoreManagerInterface $storeManager,     
        \Magento\Customer\Model\Session $sessioncustomer,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,        
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollectionFactory,
		RequestInterface $request,
		ObjectManagerInterface $objectManager,        
        array $data = []
    )
    {        
        $this->_mkProduct = $mkProduct;
        $this->_timezone = $timezone;
        $this->_storeManager = $storeManager;  
        $this->_sessioncustomer = $sessioncustomer; 
        $this->_transportBuilder = $transportBuilder;        
        $this->_scopeConfig = $scopeConfig;
        $this->inlineTranslation = $inlineTranslation;        
        $this->_messageManager = $messageManager;  
        $this->_customerCollectionFactory = $customerCollectionFactory;        
        $this->_request = $request;        
		$this->_objectManager = $objectManager;        
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {		
		try{	
            $products = $observer->getEvent()->getBunch();
            foreach($products as $product){
                $customerData = $this->_sessioncustomer->getCustomer()->getData();
                if($customerData['entity_id']){
    				$customerId = $customerData['entity_id'];
    				$_params['user_id'] = $customerId;
    			}
                $_modelOld = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\Marketplace\Model\Products')
                                                                                ->getCollection()
                                                                                ->addFieldToFilter('user_id',$_params['user_id'])
                                                                                ->addFieldToFilter('sku',$product['sku'])
                                                                                ->getFirstItem();
                if(!$_modelOld['id']) continue;
                $_model = $this->_objectManager->create('Magetop\Marketplace\Model\Products');
    			$_model->load($product['sku'],'sku')->delete();
            }      
		}catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_messageManager->addError(nl2br($e->getMessage()));            
        } catch (\Exception $e) {
            $this->_messageManager->addException($e, __('Something went wrong while saving this .').' '.$e->getMessage());            
        }		
    }
	
	public function getCustomerByEmail($email){
		$customerCollection=$this->_customerCollectionFactory->create()->addFieldToFilter('email',$email);		
		return $customerCollection;
	}
}
