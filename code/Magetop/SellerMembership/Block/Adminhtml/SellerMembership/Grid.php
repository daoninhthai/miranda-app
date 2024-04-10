<?php 
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Membership
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerMembership\Block\Adminhtml\SellerMembership;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $moduleManager;
    protected $sellermembershipCollection;
    protected $_objectmanager;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magetop\SellerMembership\Model\SellerMembershipFactory $sellermembershipFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,        
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->moduleManager = $moduleManager;
        $this->sellermembershipCollection = $sellermembershipFactory;
        $this->_objectmanager = $objectmanager;        
        parent::__construct($context, $backendHelper, $data);
    }
 
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('membershipGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('membership_record');
    }
 
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {       
        $collection = $this->sellermembershipCollection->create()->getCollection();
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }
 
    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'seller_name',
            [
                'header' => __('Seller Name'),
                'index' => 'seller_id',
                'filter' => false,
                'renderer' => 'Magetop\SellerMembership\Block\Adminhtml\Grid\Column\SellerMembershipGridSellerName'
            ]
        );
        $this->addColumn(
            'membership_id',
            [
                'header' => __('Membership'),
                'index' => 'membership_id',
                'filter' => false,
                'renderer' => 'Magetop\SellerMembership\Block\Adminhtml\Grid\Column\SellerMembershipGridMembershipName'
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header' => __('Created At'),
                'index' => 'created_at',
                'type'   => 'date',
            ]
        );
        $this->addColumn(
            'experi_date',
            [
                'header' => __('Experi Date'),
                'index' => 'experi_date',
                'type'   => 'date',
            ]
        );
        $this->addColumn(
            'remaining_number_product',
            [
                'header' => __('Remaining Number Product'),
                'index' => 'remaining_number_product',
                'type'   => 'number',
            ]
        );
        $this->addColumn(
            'paid_total',
            [
                'header' => __('Paid Total'),
                'index' => 'paid_total',
                'type'   => 'currency',
            ]
        );
        $this->addColumn(
            'paid_status',
            [
                'header' => __('Paid Status'),
                'index' => 'paid_status',
                'type'   => 'options',
                'options' => array(
                    '1'=>'Paid',
                    '0'=>'Not Paid'
                ),
                'renderer' => 'Magetop\SellerMembership\Block\Adminhtml\Grid\Column\StatusPaid'
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type'   => 'options',
                'options' => array(
                    '1'=>'Enabled',
                    '0'=>'Disabled'
                ),
                'renderer' => 'Magetop\SellerMembership\Block\Adminhtml\Grid\Column\Statuses'
            ]
        );
        
        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }
 
        return parent::_prepareColumns();
    }
    
    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');
 
        $this->getMassactionBlock()->addItem(
            'enable',
            [
                'label' => __('Enable'),
                'url' => $this->getUrl('*/*/massStatus/status/1/', ['_current' => true]),
                'confirm' => "Are you sure you wan't Enable selected items?"
            ]
        );
        $this->getMassactionBlock()->addItem(
            'disable',
            [
                'label' => __('Disable'),
                'url' => $this->getUrl('*/*/massStatus/status/0/', ['_current' => true]),
                'confirm' => "Are you sure you wan't Disable selected items?"
            ]
        );
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('*/*/delete/', ['_current' => true]),
                'confirm' => "Are you sure you wan't to delete selected items?"
            ]
        );
 
        return $this;
    }
 
    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', ['_current' => true]);
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}