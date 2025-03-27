<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Membership
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerMembership\Block\Adminhtml\Grid\Column;
use \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
class SellerMembershipGridMembershipName extends AbstractRenderer
{
    protected $_customerCollectionFactory;
    protected $_membershipCollectionFactory;
    protected $_product;
    protected $_objectmanager;
    
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magetop\SellerMembership\Model\MembershipFactory $membershipFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        $this->_customerCollectionFactory = $customerFactory;	
        $this->_membershipCollectionFactory = $membershipFactory;
        $this->_product = $product;
        $this->_objectmanager = $objectmanager;
    }
    
    public function render(\Magento\Framework\DataObject $row)
    {
        $member_ship = $this->_membershipCollectionFactory->create()->load($row->getMembershipId());
        $url = $this->_objectmanager->create('Magento\Backend\Helper\Data')->getUrl('magetop/membership/edit', array('id'=>$member_ship->getId()));
        $cell = '<a title="View Seller" href="'.$url.'">'.$member_ship->getTitle().'</a>';
        return $cell;
    }
}