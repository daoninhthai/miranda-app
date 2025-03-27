<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Store_Locator
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerStoreLocator\Block\Adminhtml\SellerStoreLocator\Edit\Tab;

class StoreAddress extends \Magento\Backend\Block\Widget\Form\Generic implements
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
    protected $_countryFactory; 

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
        \Magento\Directory\Model\Config\Source\Country $countryFactory,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_sellerstorelocatorCollection = $sellerstorelocatorCollection;
        $this->_rendererFieldset = $rendererFieldset;
        $this->_customerFactory = $customerFactory;  
        $this->_resource = $resource;  
        $this->_countryFactory = $countryFactory;
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

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Store Address')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }
        	
        $fieldset->addField(
            'address',
            'text',
            [
                'name' => 'address',
                'label' => __('Street Address'),
                'title' => __('Street Address'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'city',
            'text',
            [
                'name' => 'city',
                'label' => __('City'),
                'title' => __('City'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        $optionsc = $this->_countryFactory->toOptionArray();
        $country = $fieldset->addField(
            'country',
            'select',
            [
                'name' => 'country',
                'label' => __('Country'),
                'title' => __('Country'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'values' => $optionsc
            ]
        );
        /*
        * Add Ajax to the Country select box html output
        */
        $country->setAfterElementHtml("   
            <script type=\"text/javascript\">
                    require([
                    'jquery',
                    'mage/template',
                    'jquery/ui',
                    'mage/translate'
                ],
                function($, mageTemplate) {
                    $.ajax({
                        url : '". $this->getBaseUrl(). 'sellerstorelocator/index/RegionList/' . "country/' + $('#sellerstorelocator_country').val() + '/state/' + $('#sellerstorelocator_state').val(),
                        type: 'get',
                        dataType: 'json',
                        showLoader:true,
                        success: function(data){
                            $('#sellerstorelocator_state').empty();
                            $('#sellerstorelocator_state').replaceWith(data.htmlconent);
                        }
                    });
                    $('#edit_form').on('change', '#sellerstorelocator_country', function(event){
                        $.ajax({
                            url : '". $this->getBaseUrl(). 'sellerstorelocator/index/RegionList/' . "country/' +  $('#sellerstorelocator_country').val(),
                            type: 'get',
                            dataType: 'json',
                            showLoader:true,
                            success: function(data){
                                $('#sellerstorelocator_state').empty();
                                $('#sellerstorelocator_state').replaceWith(data.htmlconent);
                            }
                        });
                   })
                }
            );
            </script>"
        );
        $fieldset->addField(
            'state',
            'text',
            [
                'name' => 'state',
                'label' => __('State/Province'),
                'title' => __('State/Province'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'zipcode',
            'text',
            [
                'name' => 'zipcode',
                'label' => __('Zip/Postal Code'),
                'title' => __('Zip/Postal Code'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        
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
        return __('Store Address');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Store Address');
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