<?php
/**
 * Copyright © 2015  (magetop99@gmail.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * 
 */

namespace Magetop\Blog\Block\Post\View;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * Blog post related products block
 */
class RelatedProducts extends \Magento\Catalog\Block\Product\AbstractProduct
    implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $_itemCollection;

    /**
     * Catalog product visibility
     *
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_catalogProductVisibility;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * Related products block construct
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $data = []
    ) {
        $this->_catalogProductVisibility = $catalogProductVisibility;
        $this->_moduleManager = $moduleManager;
        parent::__construct($context, $data );
    }

    /**
     * Premare block data
     * @return $this
     */
    protected function _prepareCollection()
    {
        $post = $this->getPost();
        $storeId = $this->_storeManager->getStore()->getId();

        $this->_itemCollection = $post->getRelatedProducts($storeId)
            ->addAttributeToSelect('required_options');

        if ($this->_moduleManager->isEnabled('Magento_Checkout')) {
            $this->_addProductAttributesAndPrices($this->_itemCollection);
        }

        $this->_itemCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());

        $this->_itemCollection->setPageSize(
            (int) $this->_scopeConfig->getValue(
                'mfblog/post_view/related_products/number_of_products',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );

        $this->_itemCollection->getSelect()->order('rl.position', 'ASC');

        $this->_itemCollection->load();

        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }

        return $this;
    }

    /**
     * Retrieve true if Display Related Products enabled
     * @return boolean
     */
    public function displayProducts()
    {
        return (bool) $this->_scopeConfig->getValue(
            'mfblog/post_view/related_products/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getItems()
    {
        if (is_null($this->_itemCollection)) {
            $this->_prepareCollection();
        }
        return $this->_itemCollection;
    }

    /**
     * Retrieve posts instance
     *
     * @return \Magetop\Blog\Model\Category
     */
    public function getPost()
    {
        if (!$this->hasData('post')) {
            $this->setData('post',
                $this->_coreRegistry->registry('current_blog_post')
            );
        }
        return $this->getData('post');
    }

    /**
     * Get Block Identities
     * @return Array
     */
    public function getIdentities()
    {
        return [\Magento\Cms\Model\Page::CACHE_TAG . '_relatedproducts_'.$this->getPost()->getId()  ];
    }
}
