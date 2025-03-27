<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Review_Management
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/

namespace Magetop\ReviewManagement\Block\Adminhtml\Grid\Column;
use \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
class ReviewManagementGridProductName extends AbstractRenderer
{
    /**
     * Review model factory
     *
     * @var \Magento\Review\Model\ReviewFactory
     */
    protected $_reviewFactory;
    protected $_productFactory;
    protected $_objectmanager;
    
    public function __construct(
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        $this->_reviewFactory = $reviewFactory;
        $this->_productFactory = $productFactory;
        $this->_objectmanager = $objectmanager;
    }
    
    public function render(\Magento\Framework\DataObject $row)
    {
        $review = $this->_reviewFactory->create()->load($row->getReviewId());
        $product = $this->_productFactory->create()->load($review->getEntityPkValue());
        $cell = '<a href="' . $this->_objectmanager->create('Magento\Backend\Helper\Data')->getUrl('catalog/product/edit',['id' => $product->getId()]) . '" onclick="this.target=\'blank\'">' . $product->getName() . '</a>';
        return $cell;
    }
}