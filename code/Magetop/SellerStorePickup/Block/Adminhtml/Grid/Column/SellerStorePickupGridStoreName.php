<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Store_Pickup
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerStorePickup\Block\Adminhtml\Grid\Column;

class SellerStorePickupGridStoreName extends \Magento\Backend\Block\Widget\Grid\Column
{
    protected $_customerCollectionFactory;
    protected $_product;
    protected $_objectmanager;
    
    public function __construct(
        \Magetop\Marketplace\Model\SellersFactory $customerFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        $this->_customerCollectionFactory = $customerFactory;	
        $this->_product = $product;
        $this->_objectmanager = $objectmanager;
    }
    /**
     * Add to column decorated SellerStorePickupGridStoreName
     *
     * @return array
     */
    public function getFrameCallback()
    {
        return [$this, 'decorateSellerStorePickupGridStoreName'];
    }

    public function decorateSellerStorePickupGridStoreName($value, $row, $column, $isExport)
    {
        $customer = $this->_customerCollectionFactory->create()->load($row->getSellerId());
        $cell = $customer->getStoretitle();
        return $cell;
    }
}