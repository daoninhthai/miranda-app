<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Store_Locator
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerStoreLocator\Block\Adminhtml\SellerStoreLocator\Edit\Tab;

class StoreMap extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $_rendererFieldset;
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    protected $_sellerstorelocatorCollection;
    protected $_customerFactory;    
    protected $_resource;   

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magetop\SellerStoreLocator\Model\ResourceModel\SellerStoreLocator\Collection $sellerstorelocatorCollection,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        \Magento\Customer\Model\CustomerFactory $customerFactory,   
        \Magento\Framework\App\ResourceConnection $resource,  
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_sellerstorelocatorCollection = $sellerstorelocatorCollection;
        $this->_rendererFieldset = $rendererFieldset;
        $this->_customerFactory = $customerFactory;  
        $this->_resource = $resource;  
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /* @var $model \Magefan\Blog\Model\Category */
        $model = $this->_coreRegistry->registry('current_model');

        /*
         * Checking if user have permissions to save information
         */
        $isElementDisabled = !$this->_isAllowedAction('Magetop_SellerStoreLocator::sellerstorelocator');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('sellerstorelocator_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Store Map')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
        
        $fieldset->addField(
            'shop_location',
            'text',
            [
                'name' => 'shop_location',
                'label' => __('Store Location'),
                'title' => __('Store Location'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'longitude',
            'text',
            [
                'name' => 'longitude',
                'label' => __('Longitude'),
                'title' => __('Longitude'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'latitude',
            'text',
            [
                'name' => 'latitude',
                'label' => __('Latitude'),
                'title' => __('Latitude'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'zoom_level',
            'text',
            [
                'name' => 'zoom_level',
                'label' => __('Zoom Level'),
                'title' => __('Zoom Level'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'gmap', 
            'text', 
            [
                'name' => 'gmap',
                'label' => __('Map'),
                'title' => __('Map'),
                'required' => true,
                'disabled' => $isElementDisabled                 
            ]
        )->setRenderer($this->_rendererFieldset->setTemplate('Magetop_SellerStoreLocator::gmap.phtml'));   
        
        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '0' : '1');
        }                       
        $this->_eventManager->dispatch('magetop_sellerstorelocator_sellerstorelocator_edit_tab_main_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Store Map');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Store Map');
    }

    /**
     * Returns status flag about this tab can be shown or not
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
}