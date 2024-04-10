<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/

namespace Magetop\Marketplace\Block\Adminhtml;

class Reviews extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'reviews/grid.phtml';
    protected $_customerCollectionFactory;
    protected $_reviewsCollectionFactory;
    protected $_objectmanager;
    
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magetop\Marketplace\Model\ReviewsFactory $reviewsFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->_customerCollectionFactory = $customerFactory;	
        $this->_reviewsCollectionFactory = $reviewsFactory;	
        $this->_objectmanager = $objectmanager;
        parent::__construct($context, $data);
    }
 
    /**
     * Prepare button and Create reviews , edit/add reviews row and installer in Magento2
     *
     * @return \Magento\Catalog\Block\Adminhtml\Reviews
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'reviews',
            $this->getLayout()->createBlock('Magetop\Marketplace\Block\Adminhtml\Reviews\Grid', 'reviews.view.grid')
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
            'reviews/*/new'
        );
    }
 
    /**
     * Render reviews
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('reviews');
    }
}