<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Model;
class Sellers extends \Magento\Framework\Model\AbstractModel
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;
	const BASE_MEDIA_PATH = 'Magetop/Marketplace/images';
    
    protected $_eventPrefix = 'magetop_marketplace';
	protected $_productCollectionFactory;
	protected $_relatedPostsCollection;
    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'magetop_marketplace';
    protected $_url;
    
    public function __construct(
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_url = $url;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
		$this->_productCollectionFactory = $productCollectionFactory;
    }
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magetop\Marketplace\Model\ResourceModel\Sellers');
    }
    /**
     * Retrieve model title
     * @param  boolean $plural
     * @return string
     */
    public function getOwnTitle($plural = false)
    {
        return $plural ? 'Sellers' : 'Sellers';
    }
    /**
     * Retrieve true if category is active
     * @return boolean [description]
     */
    public function isActive()
    {
        return ($this->getStatus() == self::STATUS_ENABLED);
    }
    /**
     * Retrieve available category statuses
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_DISABLED => __('Disabled'), self::STATUS_ENABLED => __('Enabled')];
    }
	public function getRelatedProducts($storeId = null)
    {
        if (!$this->hasData('related_products')) {
            $collection = $this->_productCollectionFactory->create();
            if (!is_null($storeId)) {
                $collection->addStoreFilter($storeId);
            } elseif ($storeIds = $this->getStoreId()) {
                $collection->addStoreFilter($storeIds[0]);
            }
            $collection->getSelect()->joinLeft(
                ['rl' => $this->getResource()->getTable('multivendor_product')],
                'e.entity_id = rl.product_id',
                ['position']
            )->where(
                'rl.user_id = ?',
                $this->getData('user_id')
            );
			foreach ( $collection  as $product) {
				$products[$product->getId()] = ['position' => $product->getPosition()];
			}
            $this->setData('related_products', $collection);
        }
        return $this->getData('related_products');
    }
}