<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Assign_Product
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerAssignProduct\Block;
use Magento\Store\Model\ScopeInterface;
class NewProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    protected $_resource;
	protected $_product;
	protected $_modelSession;
	protected $_summaryFactory;
     
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Framework\Url\Helper\Data $urlHelper,
		\Magento\Framework\App\ResourceConnection $resource,
		\Magento\Catalog\Model\Product $product,
		\Magento\Customer\Model\Session $modelSession,
		\Magento\Review\Model\Review\SummaryFactory $summaryFactory,
        array $data = []
    ) {
		$this->_resource = $resource;
		$this->_product = $product;
		$this->_modelSession = $modelSession;
		$this->_summaryFactory = $summaryFactory;
        parent::__construct($context,$postDataHelper,$layerResolver,$categoryRepository,$urlHelper,$data);
    }
        
    protected function _getProductCollection()
    {
        $key_word = $this->getRequest()->getParam('key_search');  
        if(!$key_word) $key_word = 'qwerty';   
        
        $seller = $this->_modelSession;
		$collection = null;
		$tableMKproduct = $this->_resource->getTableName('multivendor_product');
		$collection = $this->_product->getCollection();
		$collection->addAttributeToSelect(array('*'));
		$collection->addAttributeToFilter('status',1);
		$collection->getSelect()->joinLeft(array('mk_product'=>$tableMKproduct),'e.entity_id = mk_product.product_id',array('mkproductstatus'=>"mk_product.status"))->where('mk_product.user_id!=?',$seller->getId());
        $collection->addAttributeToFilter(
            array(
                array('attribute'=> 'name','like' => '%' . $key_word . '%'),
                array('attribute'=> 'sku','like' => '%' . $key_word . '%')
            )
        );
        $litmit = $this->getRequest()->getParam('limit',5);
		if($litmit > 0){
			$collection->setPageSize($litmit);
		}
		$curPage = $this->getRequest()->getParam('p',1);
		if($curPage > 1)
		{
			$collection->setCurPage($curPage);
		}
		$this->_productCollection = $collection;
		return parent::_getProductCollection();
	}   
    
    protected function _prepareLayout()
    {
        $collection = $this->_getProductCollection();
        parent::_prepareLayout();
        if ($collection) {
            $pager = $this->getLayout()->createBlock('Magento\Theme\Block\Html\Pager','my.custom.pager');
            $pager->setAvailableLimit(array(5=>5,10=>10,20=>20,'30'=>'30')); 
            $pager->setCollection($collection);
            $this->setChild('pager', $pager);
            $collection->load();
        }
        return $this;
    }

    /**
     * @return  method for get pager html
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    } 
}