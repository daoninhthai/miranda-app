<?php 
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Attribute_Management
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerAttributeManagement\Block\Adminhtml\SellerAttributeManagement;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $moduleManager;
    protected $sellerattributemanagementCollection;
    protected $_objectmanager;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magetop\SellerAttributeManagement\Model\SellerAttributeManagementFactory $sellerattributemanagementFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,        
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->moduleManager = $moduleManager;
        $this->sellerattributemanagementCollection = $sellerattributemanagementFactory;
        $this->_objectmanager = $objectmanager;        
        parent::__construct($context, $backendHelper, $data);
    }
 
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sellerattributemanagementGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('sellerattributemanagement_record');
    }
 
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {       
        $collection = $this->sellerattributemanagementCollection->create()->getCollection();
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
            'default_label',
            [
                'header' => __('Attribute Name'),
                'index' => 'default_label',
                'type'   => 'text',
            ]
        );
        $this->addColumn(
            'attribute_code',
            [
                'header' => __('Attribute Code'),
                'index' => 'attribute_code',
                'type'   => 'text',
            ]
        );
        $this->addColumn(
            'input_type',
            [
                'header' => __('Input Type'),
                'index' => 'input_type',
                'type'   => 'options',
                'options' => array(
                    'text' => __('Text Field'),
                    'textarea' => __('Text Area'),
                    'date' => __('Date'),
                    'boolean' => __('Yes/No'),
                    'multiselect' => __('Multiple Select'),
                    'select' => __('Dropdown'),
                    'price' => __('Price'),
                    'media_image' => __('Media Image')
                )
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Show in Front'),
                'index' => 'status',
                'type'   => 'options',
                'options' => array(
                    '1'=>'Yes',
                    '0'=>'No'
                ),
                'renderer' => 'Magetop\SellerAttributeManagement\Block\Adminhtml\Grid\Column\SellerAttributeManagementGridStatus'
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
            'approve',
            [
                'label' => __('Show in Front'),
                'url' => $this->getUrl('*/*/massStatus/status/1/', ['_current' => true]),
                'confirm' => "Are you sure you wan't Show selected Attribute in Front?"
            ]
        );
        $this->getMassactionBlock()->addItem(
            'disapprove',
            [
                'label' => __('Hide from Front'),
                'url' => $this->getUrl('*/*/massStatus/status/0/', ['_current' => true]),
                'confirm' => "Are you sure you wan't Hide selected Attribute from Front?"
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