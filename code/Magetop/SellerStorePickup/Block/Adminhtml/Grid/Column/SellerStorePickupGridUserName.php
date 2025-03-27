<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Store_Pickup
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerStorePickup\Block\Adminhtml\Grid\Column;

class SellerStorePickupGridUserName extends \Magento\Backend\Block\Widget\Grid\Column
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
     * Add to column decorated SellerStorePickupGridUserName
     *
     * @return array
     */
    public function getFrameCallback()
    {
        return [$this, 'decorateSellerStorePickupGridUserName'];
    }

    public function decorateSellerStorePickupGridUserName($value, $row, $column, $isExport)
    {
        $customer = $this->_customerCollectionFactory->create()->load($row->getSellerId());
        $sellerCollection = $this->_objectmanager->create('Magetop\Marketplace\Model\ResourceModel\Sellers\Collection')->addFieldToFilter('user_id',$row->getSellerId())->getFirstItem();
        $url = $this->_objectmanager->create('Magento\Backend\Helper\Data')->getUrl('marketplace/sellers/edit', array('id'=>$sellerCollection['id']));
        $cell = '<a title="View Customer" href="'.$url.'">'.$customer->getName().'</a>';
        return $cell;
    }
}