<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */

namespace Magetop\Pslider\Block\Adminhtml\Slider\Details;

use Magetop\Pslider\Helper\Data;


/**
 * Adminhtml cms block edit form
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
	/**
	 * @var \Magento\Cms\Model\Wysiwyg\Config
	 */
	protected $_wysiwygConfig;


	/**
	 * @var \Magetop\Pslider\Model\Pslider
	 */
	protected $_status;

    protected $_helper;
	/**
	 * @param \Magento\Backend\Block\Template\Context $context
	 * @param \Magento\Framework\Registry             $registry
	 * @param \Magento\Framework\Data\FormFactory     $formFactory
	 * @param \Magento\Cms\Model\Wysiwyg\Config       $wysiwygConfig
	 * @param \Magetop\Pslider\Model\Pslider          $status
     * @param \Magetop\Pslider\Helper\Data $dataHelper
	 * @param array                                   $data
	 * @internal param \Magento\Store\Model\System\Store $systemStore
	 */
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\Data\FormFactory $formFactory,
		\Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
		\Magetop\Pslider\Model\Pslider $status,
        Data $dataHelper,
		array $data = []
	) {
		$this->_wysiwygConfig = $wysiwygConfig;
		$this->_status = $status;
        $this->_helper = $dataHelper;
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
		$this->setId('block_form');
		$this->setTitle(__('Block Information'));
	}

	/**
	 * Prepare form
	 *
	 * @return $this
	 */
	protected function _prepareForm()
	{
		$model = $this->_coreRegistry->registry('pslider_slider');
        $isElementDisabled = false;
		/** @var \Magento\Framework\Data\Form $form */
		$form = $this->_formFactory->create(
			['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
		);

		$form->setHtmlIdPrefix('slider_');

		$fieldset = $form->addFieldset(
			'base_fieldset',
			['legend' => __('General Information'), 'class' => 'fieldset-wide']
		);

		if ($model->getId()) {
			$fieldset->addField('pslider_id', 'hidden', ['name' => 'pslider_id']);
		}

        $fieldset->addField(
            'title',
            'text',
            [
                'name' => 'title',
                'label' => __('Title'),
                'title' => __('Title'),
                'required' => true,
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'status',
            'select',
            [
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'status',
                'required' => true,
                'options' => $this->_helper->getAvailableStatuses(),
                'disabled' => $isElementDisabled
            ]
        );
        if (!$model->getId()) {
            $model->setData('status', $isElementDisabled ? '0' : '1');
        }
        $fieldset->addField(
            'category_id',
            'select',
            [
                'name' => 'category_id',
                'label' => __('Category'),
                'title' => __('Category'),
                'options' => $this->_helper->getAvaibleCategories(),
                'disabled' => $isElementDisabled
            ]
        );
        $fieldset->addField(
            'type_id',
            'select',
            [
                'name' => 'type_id',
                'label' => __('Type'),
                'title' => __('Type'),
                'required' => true,
                'options' => $this->_helper->getAvaibleType(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'cats_id',
            'select',
            [
                'name' => 'cats_id',
                'label' => __('Group'),
                'title' => __('Group'),
                'required' => true,
                'options' => $this->_helper->getAvaibleGroupsSlider(),
                'disabled' => $isElementDisabled
            ]
        );

        $fieldset->addField(
            'position',
            'text',
            [
                'name' => 'position',
                'label' => __('Position'),
                'title' => __('Position'),
                'disabled' => $isElementDisabled
            ]
        );


        $fieldset->addField(
			'description',
			'textarea',
			[
				'name' => 'description',
				'label' => __('Description'),
				'title' => __('Description'),
				'disabled' => $isElementDisabled,
			]
		);
		$form->setValues($model->getData());
		$form->setUseContainer(true);
		$this->setForm($form);

		return parent::_prepareForm();
	}
}