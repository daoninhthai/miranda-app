<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Review_Management
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/

namespace Magetop\ReviewManagement\Block\Adminhtml;

class ReviewManagement extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'reviewmanagement/view.phtml';
    protected $_customerCollectionFactory;
    protected $_reviewmanagementCollectionFactory;
    protected $_objectmanager;
    
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magetop\ReviewManagement\Model\ReviewManagementFactory $reviewmanagementFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->_customerCollectionFactory = $customerFactory;	
        $this->_reviewmanagementCollectionFactory = $reviewmanagementFactory;	
        $this->_objectmanager = $objectmanager;
        parent::__construct($context, $data);
    }
 
    /**
     * Prepare button and Create reviewmanagement , edit/add reviewmanagement row and installer in Magento2
     *
     * @return \Magento\Catalog\Block\Adminhtml\ReviewManagement
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'reviewmanagement',
            $this->getLayout()->createBlock('Magetop\ReviewManagement\Block\Adminhtml\ReviewManagement\Grid', 'reviewmanagement.view.grid')
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
            'reviewmanagement/*/new'
        );
    }
 
    /**
     * Render reviewmanagement
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('reviewmanagement');
    }
}