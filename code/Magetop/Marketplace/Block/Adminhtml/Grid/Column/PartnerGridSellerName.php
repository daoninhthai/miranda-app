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
class PartnerGridSellerName extends AbstractRenderer
{
    protected $_customerCollectionFactory;
    protected $_objectmanager;
    
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        $this->_customerCollectionFactory = $customerFactory;	
        $this->_objectmanager = $objectmanager;
    }
    
    public function render(\Magento\Framework\DataObject $row)
    {
        $customer = $this->_customerCollectionFactory->create()->load($row->getSellerid());
        $sellerCollection = $this->_objectmanager->create('Magetop\Marketplace\Model\ResourceModel\Sellers\Collection')->addFieldToFilter('user_id',$row->getSellerid())->getFirstItem();
        $url = $this->_objectmanager->create('Magento\Backend\Helper\Data')->getUrl('marketplace/sellers/edit', array('id'=>$sellerCollection['id']));
        $cell = '<a title="View Customer" href="'.$url.'">'.$customer->getName().'</a>';
        return $cell;
    }
}