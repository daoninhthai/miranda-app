<?php 
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Review_Management
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
 
namespace Magetop\ReviewManagement\Block\Adminhtml\ReviewManagement;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $moduleManager;
    protected $_reviewmanagementCollection;
    protected $_objectmanager;
    
    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magetop\ReviewManagement\Model\ReviewManagementFactory $reviewmanagementFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,        
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->moduleManager = $moduleManager;
        $this->_reviewmanagementCollection = $reviewmanagementFactory;
        $this->_objectmanager = $objectmanager;        
        parent::__construct($context, $backendHelper, $data);
    }
    
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('reviewmanagementGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('reviewmanagement_record');
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->_reviewmanagementCollection->create()->getCollection()->addFieldToFilter('type','comment');
        $this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    /**
     * Prepare product attributes grid columns
     *
     * @return $this
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
            'review_id',
            [
                'header' => __('Review'),
                'index' => 'review_id',
                'filter' => false,
                'renderer' => 'Magetop\ReviewManagement\Block\Adminhtml\Grid\Column\ReviewManagementGridReviewName'
            ]
        );
        $this->addColumn(
            'product',
            [
                'header' => __('Product'),
                'index' => 'review_id',
                'filter' => false,
                'renderer' => 'Magetop\ReviewManagement\Block\Adminhtml\Grid\Column\ReviewManagementGridProductName'
            ]
        );
        $this->addColumn(
            'type',
            [
                'header' => __('Type'),
                'index' => 'type',
                'type'   => 'text',
            ]
        );
        $this->addColumn(
            'content',
            [
                'header' => __('Content'),
                'index' => 'content',
                'type'   => 'text',
            ]
        );
        $this->addColumn(
            'post_by',
            [
                'header' => __('Post By'),
                'index' => 'post_by',
                'type'   => 'text',
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header' => __('Post Time'),
                'index' => 'created_at',
                'type'   => 'datetime',
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type'   => 'options',
                'options' => array(
                    '1'=>'Approve',
                    '0'=>'Disapprove'
                ),
                'renderer' => 'Magetop\ReviewManagement\Block\Adminhtml\Grid\Column\ReviewManagementGridStatus'
            ]
        );
        return $this;
    }
    
    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');
        
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl('*/*/delete/', ['_current' => true]),
                'confirm' => "Are you sure you wan't to delete selected item?"
            ]
        );
        $this->getMassactionBlock()->addItem(
            'enable',
            [
                'label' => __('Approve'),
                'url' => $this->getUrl('*/*/massStatus/status/1/', ['_current' => true]),
                'confirm' => "Are you sure you wan't to Approve selected item?"
            ]
        );
        $this->getMassactionBlock()->addItem(
            'disable',
            [
                'label' => __('Disapprove'),
                'url' => $this->getUrl('*/*/massStatus/status/0/', ['_current' => true]),
                'confirm' => "Are you sure you wan't to Disapprove selected item?"
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
        return '#';
    }
}