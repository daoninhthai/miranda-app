<?php 
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Coupon
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerCoupon\Block\Adminhtml\SellerCoupon;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $moduleManager;
    protected $sellercouponCollection;
    protected $_objectmanager;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magetop\SellerCoupon\Model\SellerCouponFactory $sellercouponFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,        
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->moduleManager = $moduleManager;
        $this->sellercouponCollection = $sellercouponFactory;
        $this->_objectmanager = $objectmanager;        
        parent::__construct($context, $backendHelper, $data);
    }
 
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('couponGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('coupon_record');
    }
 
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {       
        $collection = $this->sellercouponCollection->create()->getCollection();
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
                'renderer' => 'Magetop\SellerCoupon\Block\Adminhtml\Grid\Column\SellerCouponGridSellerName'
            ]
        );
        $this->addColumn(
            'seller_coupon_code',
            [
                'header' => __('Coupon Code'),
                'index' => 'seller_coupon_code',
                'type'   => 'text',
            ]
        );
        $this->addColumn(
            'seller_coupon_type',
            [
                'header' => __('Coupon Type'),
                'index' => 'seller_coupon_type',
                'type'   => 'options',
                'options' => array(
                    '1'=>'Coupon Price',
                    '2'=>'Coupon Percent'
                )
            ]
        );
        $this->addColumn(
            'seller_coupon_price',
            [
                'header' => __('Coupon Value'),
                'index' => 'seller_coupon_price',
                'filter' => false,
                'renderer' => 'Magetop\SellerCoupon\Block\Adminhtml\Grid\Column\SellerCouponGridValue'
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header' => __('From Date'),
                'index' => 'created_at',
                'type'   => 'date',
            ]
        );
        $this->addColumn(
            'expire_date',
            [
                'header' => __('Expire Date'),
                'index' => 'expire_date',
                'type'   => 'date',
            ]
        );
        $this->addColumn(
            'used_description',
            [
                'header' => __('Used Description'),
                'index' => 'used_description',
                'type'   => 'text',
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
                'renderer' => 'Magetop\SellerCoupon\Block\Adminhtml\Grid\Column\Statuses'
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