<?php
/**
 * @author      Magetop
 * @package     Magetop_Api
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Api\Controller\Marketplace;

use Magento\Quote\Api\Data\EstimateAddressInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Event\Manager as EventManager;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Magetop\Api\Helper\Data as DataHelper;

class GetSellerProfile extends \Magetop\Api\Controller\AbstractController
{
    protected $_sellersCollectionFactory;
    protected $_resource;
	protected $_product;
	protected $_modelSession;
	protected $_summaryFactory;
    
    public function __construct(
        \Magetop\Marketplace\Model\ResourceModel\Sellers\CollectionFactory $sellersCollectionFactory,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Catalog\Model\Product $product,
		\Magento\Customer\Model\Session $modelSession,
		\Magento\Review\Model\Review\SummaryFactory $summaryFactory,
        Context $context,
        EventManager $eventManager,
        AppEmulation $appEmulation,
        DataHelper $dataHelper
    ) {
        $this->_sellersCollectionFactory = $sellersCollectionFactory;
		$this->_resource = $resource;
		$this->_product = $product;
		$this->_modelSession = $modelSession;
		$this->_summaryFactory = $summaryFactory;
        parent::__construct($context, $eventManager, $appEmulation, $dataHelper);
    }
    
    /**
     * execute category list.
     *
     * @return \Magento\Framework\Controller\ResultFactory::TYPE_JSON
     */
    public function execute(){
        parent::execute();

        $responseData = [];
        $status = true;
        $message = 'Successfully!';
        $data = [];

        try{
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $customerSession = $objectManager->get('Magento\Customer\Model\Session');
            if($customerSession->isLoggedIn()) {
                $customerId = $customerSession->getCustomer()->getId();
            }
            $_sellerInfomation = $this->getSellerInfomation($customerId);
            $data = array(
                'profile' => $_sellerInfomation
            );
        }catch(\Exception $e){
            $status = false;
            $message = $e->getMessage();
        }
        $responseData = $this->getResponseData($status, $message, $data);
        
        return $this->returnResultJson($responseData);
    }
    public function getSellerInfomation($_customerId){	
		$_data = self::getSellerCollection($_customerId);
		return $_data[0];
	}
    protected function getSellerCollection($_customerId){
		$collection = $this->_sellersCollectionFactory->create()->addFieldToFilter('user_id',$_customerId);				
		return $collection->getData();
	}
}