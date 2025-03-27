<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Product_Attribute_Management
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/

namespace Magetop\ProductAttributeManagement\Block\Adminhtml;

class ProductAttributeManagement extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'productattributemanagement/view.phtml';
    protected $_customerCollectionFactory;
    protected $_productattributemanagementCollectionFactory;
    protected $_objectmanager;
    
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magetop\ProductAttributeManagement\Model\ProductAttributeManagementFactory $productattributemanagementFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->_customerCollectionFactory = $customerFactory;	
        $this->_productattributemanagementCollectionFactory = $productattributemanagementFactory;	
        $this->_objectmanager = $objectmanager;
        parent::__construct($context, $data);
    }
 
    /**
     * Prepare button and Create productattributemanagement , edit/add productattributemanagement row and installer in Magento2
     *
     * @return \Magento\Catalog\Block\Adminhtml\ProductAttributeManagement
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'productattributemanagement',
            $this->getLayout()->createBlock('Magetop\ProductAttributeManagement\Block\Adminhtml\ProductAttributeManagement\Grid', 'productattributemanagement.view.grid')
        );
        return parent::_prepareLayout();
    }
                
    /**
     *
     *
     * @param string $type
     * @return string
     */
    protected function _getCreateUrl()
    {
        return $this->getUrl(
            'productattributemanagement/*/new'
        );
    }
 
    /**
     * Render productattributemanagement
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('productattributemanagement');
    }
}