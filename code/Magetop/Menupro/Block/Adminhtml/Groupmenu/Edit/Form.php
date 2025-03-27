<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Menupro\Block\Adminhtml\Groupmenu\Edit;

/**
 * Adminhtml custom block edit form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magetop\Menupro\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magetop\Menupro\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magetop\Menupro\Model\Groupmenu\Source\Options $groupmenuOptions,
        //\Magento\Store\Model\System\Store $systemStore,
        array $data = []
    ) {
        $this->_groupmenuOptions = $groupmenuOptions;
        //$this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Init form
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('form');
        $this->setTitle(__('Groupmenu Information'));
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('groupmenu');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );

        $form->setHtmlIdPrefix('groupmenu_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Groupmenu Information'), 'class' => 'fieldset-wide']
        );

        if ($model->getGroupmenuId()) {
            $fieldset->addField('groupmenu_id', 'hidden', ['name' => 'groupmenu_id']);
        }

        $fieldset->addField(
            'title',
            'text',
            ['name' => 'title', 'label' => __('Title'), 'title' => __('Title'), 'required' => true]
        );
        
        $fieldset->addField(
            'animation',
            'select',
            [
                'name' => 'animation',
                'label' => __('Animations'),
                'title' => __('Animations'),
				'options' => $this->_groupmenuOptions->toAnimationOption(),
                'required' => true                
            ]
        );                     
		$fieldset->addField(
            'position',
            'select',
            [
                'name' => 'position',
                'label' => __('Positions'),
                'title' => __('Positions'),
				'options' => $this->_groupmenuOptions->toPositionsnOption(),
                'required' => true                
            ]
        );
		$fieldset->addField(
            'responsive',
            'select',
            [
                'name' => 'responsive',
                'label' => __('Responsive'),
                'title' => __('Responsive'),
				'options' => $this->_groupmenuOptions->toResponsiveOption(),
                'required' => true                
            ]
        );
		$fieldset->addField(
            'color',
            'text',
            [
                'name' => 'color',
                'label' => __('Color schemes '),
                'title' => __('Color schemes '),
				'class' => 'mb-color',
                'required' => true                
            ]
        );

        $fieldset->addField(
            'enable_sticky',
            'select',
            [
                'label' => __('Enable sticky menu'),
                'title' => __('Enable sticky menu'),
                'name' => 'enable_sticky',
                'required' => true,
                'options' => [
								'1' => __('Enabled'),
								'0' => __('Disabled')
							 ]
            ]
        );
		$fieldset->addField(
            'sub_menu_trigger',
            'select',
            [
                'label' => __('Sub-Menu Trigger Click'),
                'title' => __('Sub-Menu Trigger Click'),
                'name' => 'sub_menu_trigger',
                'required' => true,
                'options' => [
								'1' => __('Enabled'),
								'0' => __('Disabled')
							 ]
            ]
        );
		$fieldset->addField(
            'is_active',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'is_active',
                'required' => true,
                'options' => [
								'1' => __('Enabled'),
								'0' => __('Disabled')
							 ]
            ]
        );
        
        if (!$model->getId()) {
            $model->setData('is_active', '1');
        }

		$fieldset->addField(
            'description',
            'textarea',
            [
                'name' => 'description',
				'readonly'	=> true,
                'label' => __('How to embed?'),
                'title' => __('How to embed?'),
				'after_element_html'	=> '<small class="help-install" style="color: red; font-size: 20px;"><div class="config-heading">Press "Save And Continue Edit" to saved and get the embed code(XML or Widget block)</div></small>'.'<small class="help-note" style="display:none;"><div class="config-heading">Copy the embed code to replace the default menu or any position where you want to display this menu group ( 3 options).</div></small>',
                'style' => 'height:12em',
				'wysiwyg'   => false,
                'required' => false
            ]
        );
        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
