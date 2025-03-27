<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Attribute_Management
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerAttributeManagement\Block\Adminhtml\SellerAttributeManagement\Edit\Tab;

class Main extends \Magento\Backend\Block\Widget\Form\Generic implements \Magento\Backend\Block\Widget\Tab\TabInterface {
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    protected $_newsCollection;
    /**
     * @var \Magento\Backend\Block\Widget\Form\Renderer\Fieldset
     */
    protected $_rendererFieldset;

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
        \Magetop\SellerAttributeManagement\Model\ResourceModel\SellerAttributeManagement\Collection $newsCollection,
		\Magento\Backend\Helper\Data $helper,
        \Magento\Backend\Block\Widget\Form\Renderer\Fieldset $rendererFieldset,
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        $this->_newsCollection = $newsCollection;
        $this->_helper = $helper;
        $this->_rendererFieldset = $rendererFieldset;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_model');

        /*
         * Checking if user have permissions to save information
         */
        $isElementDisabled = !$this->_isAllowedAction('Magetop_SellerAttributeManagement::sellerattributemanagement');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $form->setHtmlIdPrefix('sellerattributemanagement_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Seller Attribute Properties')]);

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', ['name' => 'id']);
        }

		$fieldset->addField(
            'default_label',
            'text',
            [
                'name' => 'default_label',
                'label' => __('Default Label'),
                'title' => __('Default Label'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'attribute_code',
            'text',
            [
                'name' => 'attribute_code',
                'label' => __('Attribute Code'),
                'title' => __('Attribute Code'),
                'required' => true,
                'disabled' => $model->getId()?'disabled':''
            ]
        );
        $fieldset->addField(
            'input_type',
            'select',
            [
                'label' => __('Input Type'),
                'title' => __('Input Type'),
                'name' => 'input_type',
                'required' => false,
                'options' => $this->getOptionInputType(),
                'disabled' => $isElementDisabled
            ]
        ); 
        $fieldset->addField(
            'required',
            'select',
            [
                'label' => __('Values Required'),
                'title' => __('Values Required'),
                'name' => 'required',
                'required' => false,
                'options' => $this->getOptionYesNo(),
                'disabled' => $isElementDisabled
            ]
        );  
        $fieldset->addField(
            'validate',
            'select',
            [
                'label' => __('Input Validation'),
                'title' => __('Input Validation'),
                'name' => 'validate',
                'required' => true,
                'options' => $this->getOptionYesNo(),
                'disabled' => $isElementDisabled
            ]
        );  
        $fieldset->addField(
            'sort_order',
            'text',
            [
                'name' => 'sort_order',
                'label' => __('Sort Order'),
                'title' => __('Sort Order'),
                'required' => true,
                'class' => 'validate-number',
                'disabled' => $isElementDisabled
            ]
        ); 
                        
        $fieldset = $form->addFieldset('base_fieldset_options', ['legend' => __('Manage Options (Values of Your Attribute)')]);
        $data = $model->getData();
        $fieldset->addField(
            'manage_options', 
            'text', 
            [
                'name' => 'manage_options',
                'label' => __('Manage Options (Values of Your Attribute)'),
                'title' => __('Manage Options (Values of Your Attribute)'),
                'required' => true,
                'disabled' => $isElementDisabled                 
            ]
        )->setRenderer($this->_rendererFieldset->setTemplate('Magetop_SellerAttributeManagement::sellerattributemanagement/options.phtml')->setOptionData($data));  
		
        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '0' : '1');
        }                       
        $this->_eventManager->dispatch('magetop_marketplace_sellerattributemanagement_edit_tab_main_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());
        $this->setForm($form);

        return parent::_prepareForm();
    }
    
    public function getOptionYesNo()
    {
        return [1 => __('Yes'),0 => __('No')];
    }
    
    public function getStatusYesNo()
    {
        return [1 => __('Enable'),0 => __('Disable')];
    }
    
    public function getOptionInputType()
    {
        return [
            'text' => __('Text Field'),
            'textarea' => __('Text Area'),
            'date' => __('Date'),
            'boolean' => __('Yes/No'),
            'multiselect' => __('Multiple Select'),
            'select' => __('Dropdown'),
            'price' => __('Price'),
            'media_image' => __('Media Image')
        ];
    }

    /**
     * Prepare label for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Properties');
    }

    /**
     * Prepare title for tab
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Properties');
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