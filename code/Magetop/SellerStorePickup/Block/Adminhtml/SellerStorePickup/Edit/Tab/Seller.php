<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Store_Pickup
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerStorePickup\Block\Adminhtml\SellerStorePickup\Edit\Tab;
 
use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;

class Seller extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magetop\SellerStorePickup\Model\GridFactory
     */
    protected $storePickupFactory;
    protected $_customerCollectionFactory;                                               

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
		\Magetop\SellerStorePickup\Model\SellerStorePickupFactory $storePickupFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,                                                                                        
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->storePickupFactory = $storePickupFactory;
        $this->_customerCollectionFactory = $customerFactory;                                                                                        
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('messages_tab_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
		$seller_id = $this->getRequest()->getParam('id');
        $seller =  \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\Marketplace\Model\Sellers')->load($seller_id);
		$collection = $this->storePickupFactory->create()->getCollection()->addFieldToFilter('seller_id',$seller->getUserId());      
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('Id'),
                'sortable' => true,
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'title',
            [
                'header' => __('Store Name'),
                'sortable' => true,
                'index' => 'title',
            ]
        );
        $this->addColumn(
            'shop_location',
            [
                'header' => __('Store Location'),
                'sortable' => true,
                'index' => 'shop_location',
            ]
        );
        $this->addColumn(
            'longitude',
            [
                'header' => __('Longitude'),
                'sortable' => true,
                'index' => 'longitude',
            ]
        );
        $this->addColumn(
            'latitude',
            [
                'header' => __('Latitude'),
                'sortable' => true,
                'index' => 'latitude',
            ]
        );
        $this->addColumn(
            'edit',
            [
                'header' => __('Edit'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('Edit'),
                        'url' => [
                            'base' => '*/*/edit',
                            'params' => ['store' => $this->getRequest()->getParam('store')]
                        ],
                        'field' => 'id'
                    ]
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action'
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('magetop/sellerstorepickup/seller', ['_current' => true]);
    }
}