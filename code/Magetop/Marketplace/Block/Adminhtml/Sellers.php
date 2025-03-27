<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/

namespace Magetop\Marketplace\Block\Adminhtml;

class Sellers extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'sellers/grid.phtml';
    protected $_customerCollectionFactory;
    protected $_sellersCollectionFactory;
    protected $_objectmanager;
    
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magetop\Marketplace\Model\SellersFactory $sellersFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->_customerCollectionFactory = $customerFactory;	
        $this->_sellersCollectionFactory = $sellersFactory;	
        $this->_objectmanager = $objectmanager;
        parent::__construct($context, $data);
    }
 
    /**
     * Prepare button and Create sellers , edit/add sellers row and installer in Magento2
     *
     * @return \Magento\Catalog\Block\Adminhtml\Sellers
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'sellers',
            $this->getLayout()->createBlock('Magetop\Marketplace\Block\Adminhtml\Sellers\Grid', 'sellers.view.grid')
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
            'sellers/*/new'
        );
    }
 
    /**
     * Render sellers
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('sellers');
    }
}