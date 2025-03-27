<?php 
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Vacation
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
 
namespace Magetop\SellerVacation\Block\Adminhtml\SellerVacation;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $moduleManager;
    protected $sellervacationCollection;
    protected $_objectmanager;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magetop\SellerVacation\Model\SellerVacationFactory $sellervacationFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,        
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        array $data = []
    ) {
        $this->moduleManager = $moduleManager;
        $this->sellervacationCollection = $sellervacationFactory;
        $this->_objectmanager = $objectmanager;        
        parent::__construct($context, $backendHelper, $data);
    }
 
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sellervacationGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('ASC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
        $this->setVarNameFilter('sellervacation_record');
    }
 
    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->sellervacationCollection->create()->getCollection();
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
            'seller_id',
            [
                'header' => __('Seller Name'),
                'index' => 'seller_id',
                'filter' => false,
                'renderer' => 'Magetop\SellerVacation\Block\Adminhtml\Grid\Column\SellerVacationGridSellerName'
            ]
        );
        $this->addColumn(
            'vacation_message',
            [
                'header' => __('Vacation Message'),
                'index' => 'vacation_message',
                'type'   => 'text',
            ]
        );
        $this->addColumn(
            'date_from',
            [
                'header' => __('Date From'),
                'index' => 'date_from',
                'type'	=> 'datetime',
            ]
        );
        $this->addColumn(
            'date_to',
            [
                'header' => __('Date To'),
                'index' => 'date_to',
                'type'   => 'datetime',
            ]
        );
        $this->addColumn(
            'text_add_cart',
            [
                'header' => __('Text Add Cart'),
                'index' => 'text_add_cart',
                'type'   => 'text',
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header' => __('Created'),
                'index' => 'created_at',
                'type'   => 'datetime',
            ]
        );
        $this->addColumn(
            'updated_at',
            [
                'header' => __('Updated'),
                'index' => 'updated_at',
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
                    '1'=>'Enabled',
                    '0'=>'Disabled'
                ),
                'renderer' => 'Magetop\SellerVacation\Block\Adminhtml\Grid\Column\SellerVacationGridStatus'
            ]
        );
      
 
        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }
 
        return parent::_prepareColumns();
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