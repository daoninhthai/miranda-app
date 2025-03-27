<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Membership
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerMembership\Block;
class Lists extends \Magento\Catalog\Block\Product\ListProduct
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
		\Magento\Review\Model\Review\SummaryFactory $summaryFactory,
        array $data = []
    ) {
		$this->_resource = $resource;
		$this->_product = $product;
		$this->_summaryFactory = $summaryFactory;
        parent::__construct($context,$postDataHelper,$layerResolver,$categoryRepository,$urlHelper,$data);
		
    }
	protected function _getProductCollection()
	{
		$collection = null;
		$orderBy = $this->getRequest()->getParam('product_list_order','id');
		$sortOrder = $this->getRequest()->getParam('product_list_dir','ASC');
        $limit = $this->getRequest()->getParam('product_list_limit',9);
		$curPage = $this->getRequest()->getParam('p',1);
        
		$tableMKmembership = $this->_resource->getTableName('multivendor_seller_membership');
		$collection = $this->_product->getCollection();
		$collection->addAttributeToSelect(array('*'));
		$collection->addAttributeToFilter('status',1);
		$collection->getSelect()->joinLeft(
            array('mk_membership'=>$tableMKmembership),'e.entity_id = mk_membership.product_id',
            array('price'=>'mk_membership.fee','mk_membership.*')
        )->where('mk_membership.status = 1');
        if($orderBy){
            $collection->addAttributeToSort($orderBy,$sortOrder);
        }
        if($limit > 0){
			$collection->setPageSize($limit);
		}
		if($curPage > 1){
			$collection->setCurPage($curPage);
		}
		$this->_productCollection = $collection;
		return parent::_getProductCollection();
	}
	public function getLoadedProductCollection()
    {
        return $this->_getProductCollection();
    }
}