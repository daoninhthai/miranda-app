<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Store_Locator
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerStoreLocator\Block\Adminhtml\Grid\Column;

class SellerStoreLocatorGridUserName extends \Magento\Backend\Block\Widget\Grid\Column
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
    /**
     * Add to column decorated SellerStoreLocatorGridUserName
     *
     * @return array
     */
    public function getFrameCallback()
    {
        return [$this, 'decorateSellerStoreLocatorGridUserName'];
    }

    public function decorateSellerStoreLocatorGridUserName($value, $row, $column, $isExport)
    {
        $customer = $this->_customerCollectionFactory->create()->load($row->getSellerId());
        $url = $this->_objectmanager->create('Magento\Backend\Helper\Data')->getUrl('marketplace/sellers/edit', array('id'=>$row->getSellerId()));
        $cell = '<a title="View Customer" href="'.$url.'">'.$customer->getName().'</a>';
        return $cell;
    }
}