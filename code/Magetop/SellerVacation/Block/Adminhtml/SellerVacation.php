<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Vacation
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/

namespace Magetop\SellerVacation\Block\Adminhtml;

class SellerVacation extends \Magento\Backend\Block\Widget\Container
{
    /**
     * @var string
     */
    protected $_template = 'sellervacation/view.phtml';
    protected $_customerCollectionFactory;
    protected $_sellervacationCollectionFactory;
    protected $_objectmanager;
    
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magetop\SellerVacation\Model\SellerVacationFactory $sellervacationFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Backend\Block\Widget\Context $context,
        array $data = []
    ) {
        $this->_customerCollectionFactory = $customerFactory;	
        $this->_sellervacationCollectionFactory = $sellervacationFactory;	
        $this->_objectmanager = $objectmanager;
        parent::__construct($context, $data);
    }
 
    /**
     * Prepare button and Create sellervacation , edit/add sellervacation row and installer in Magento2
     *
     * @return \Magento\Catalog\Block\Adminhtml\SellerVacation
     */
    protected function _prepareLayout()
    {
        $this->setChild(
            'sellervacation',
            $this->getLayout()->createBlock('Magetop\SellerVacation\Block\Adminhtml\SellerVacation\Grid', 'sellervacation.view.grid')
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
            'sellervacation/*/new'
        );
    }
 
    /**
     * Render sellervacation
     *
     * @return string
     */
    public function getGridHtml()
    {
        return $this->getChildHtml('sellervacation');
    }
}