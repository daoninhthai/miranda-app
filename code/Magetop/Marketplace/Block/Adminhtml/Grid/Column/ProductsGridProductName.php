<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Block\Adminhtml\Grid\Column;
use \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
class ProductsGridProductName extends AbstractRenderer
{
    protected $_customerCollectionFactory;
    protected $_product;
    protected $_objectmanager;

    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        $this->_customerCollectionFactory = $customerFactory;
        $this->_product = $product;
        $this->_objectmanager = $objectmanager;
    }

    public function render(\Magento\Framework\DataObject $row)
    {
        $product = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Catalog\Model\Product')->load($row->getProductId());
        $url = $this->_objectmanager->create('Magento\Backend\Helper\Data')->getUrl('catalog/product/edit', array('id'=>$product->getId()));
        $cell = '<a title="View Product" href="'.$url.'">'.$product->getName().'</a>';
        return $cell;
    }
}
