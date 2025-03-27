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

class GetSellerProducts extends \Magetop\Api\Controller\AbstractController
{
    protected $_imageProduct;
    protected $_resource;
	protected $_product;
	protected $_modelSession;
	protected $_summaryFactory;

    public function __construct(
        \Magento\Catalog\Helper\Image $imageProduct,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Catalog\Model\Product $product,
		\Magento\Customer\Model\Session $modelSession,
		\Magento\Review\Model\Review\SummaryFactory $summaryFactory,
        Context $context,
        EventManager $eventManager,
        AppEmulation $appEmulation,
        DataHelper $dataHelper
    ) {
        $this->_imageProduct = $imageProduct;
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
            $_productCollection = $this->_getProductCollection($customerId);

            $productsData = array();
            if ($_productCollection->count()){
                foreach($_productCollection as $product){
                    switch($product->getMkproductstatus()){
                        case 0 :
                            $approval = __('PENDING');
                        break;
                        case 1 :
                            $approval = __('APPROVED');
                        break;
                        case 2 :
                            $approval = __('UNAPPROVED');
                        break;
                        case 3 :
                            $approval = __('ACTIVE');
                        break;
                        case 4 :
                            $approval = __('INACTIVE');
                        break;
                        case 5 :
                            $approval = __('NOT SUBMITTED');
                        break;
                    }
                    $productsData[] = array(
                        'product-id' => $product->getId(),
                        'product-thumbnail' => $this->_imageProduct->init($product, 'product_thumbnail_image')->getUrl(),
                        'product-type' => $product->getTypeId(),
                        'product-name' => $product->getName(),
                        'product-attribute-set' => $objectManager->create('\Magento\Eav\Api\AttributeSetRepositoryInterface')->get($product->getAttributeSetId())->getAttributeSetName(),
                        'product-sku' => $product->getSku(),
                        'product-price' => \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\Pricing\Helper\Data')->currency(@number_format($product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue(),2),true,false),
                        'product-status' => $product->getStatus()==1?__('Enabled'):__('Disabled'),
                        'product-approval' => $approval,
                        'product-qty' => \Magento\Framework\App\ObjectManager::getInstance()->get('\Magento\CatalogInventory\Api\StockStateInterface')->getStockQty( $product->getId(), $product->getStore()->getWebsiteId())
                    );
                }
            }
            $data = array(
                'products' => $productsData
            );
        }catch(\Exception $e){
            $status = false;
            $message = $e->getMessage();
        }
        $responseData = $this->getResponseData($status, $message, $data);

        return $this->returnResultJson($responseData);
    }
	protected function _getProductCollection($customerId)
	{
		$collection = null;
		$orderBy = $this->getRequest()->getParam('product_list_order','position');
		$sortOrder = $this->getRequest()->getParam('product_list_dir','ASC');
        $seller_search = $this->getRequest()->getParam('seller_search',null);
        $litmit = $this->getRequest()->getParam('product_list_limit',9);
		$curPage = $this->getRequest()->getParam('p',1);
		if($customerId)
		{
			$customerSession = $this->_modelSession;
			$tableMKproduct = $this->_resource->getTableName('multivendor_product');
			$collection = $this->_product->getCollection();
			$collection->addAttributeToSelect(array('*'));
            $collection->addAttributeToFilter('status',1);
            $collection->addAttributeToFilter('visibility', array('in' => array(2,3,4)));
			if($customerSession->isLoggedIn()){

			}else{
				$collection->addAttributeToFilter('status',1);
			}
			$collection->getSelect()->joinLeft(array('mk_product'=>$tableMKproduct),'e.entity_id = mk_product.product_id',array('mkproductstatus'=>"mk_product.status"))
				->where('mk_product.user_id=?',$customerId)
                ->where('mk_product.status = 1');
			//$collection->addAttributeToSort($orderBy,$sortOrder);
            if($seller_search){
                $collection->addAttributeToFilter('name', array('like' => '%'.$seller_search.'%'));
            }
			if($litmit > 0)
			{
				$collection->setPageSize($litmit);
			}
			if($curPage > 1)
			{
				$collection->setCurPage($curPage);
			}
		}
		return $collection;
	}
}
