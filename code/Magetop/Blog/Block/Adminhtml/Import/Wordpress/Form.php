<?php
/**
 * Copyright © 2016  (magetop99@gmail.com). All rights reserved.
 * See LICENSE.txt for license details (http://opensource.org/licenses/osl-3.0.php).
 *
 * 
 */

namespace Magetop\Blog\Block\Adminhtml\Import\Wordpress;

/**
 * Wordpress import form block
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

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
        array $data = []
    ) {
        $this->_systemStore = $systemStore;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        //Magento\Backend\Model\Session
        $form = $this->_formFactory->create(
            ['data' => ['id' => 'edit_form', 'action' => $this->getData('action'), 'method' => 'post']]
        );
        $form->setUseContainer(true);

        $data = $this->_coreRegistry->registry('import_config')->getData();

        /*
         * Checking if user have permissions to save information
         */
        if ($this->_authorization->isAllowed('Magetop_Blog::import')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }
        $isElementDisabled = false;


        $form->setHtmlIdPrefix('import_');

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => '']);

        $fieldset->addField(
            'type',
            'hidden',
            [
                'name' => 'type',
                'required' => true,
                'disabled' => $isElementDisabled,
            ]
        );

        $fieldset->addField(
            'dbname',
            'text',
            [
                'name' => 'dbname',
                'label' => __('Database Name'),
                'title' => __('Database Name'),
                'required' => true,
                'disabled' => $isElementDisabled,
                'after_element_html' => '<small>The name of the database you run in WP.</small>',
            ]
        );

        $fieldset->addField(
            'uname',
            'text',
            [
                'label' => __('User Name'),
                'title' => __('User Name'),
                'name' => 'uname',
                'required' => true,
                'disabled' => $isElementDisabled,
                'after_element_html' => '<small>Your WP MySQL username.</small>',
            ]
        );

        $fieldset->addField(
            'pwd',
            'text',
            [
                'label' => __('Password'),
                'title' => __('Password'),
                'name' => 'pwd',
                'required' => true,
                'disabled' => $isElementDisabled,
                'after_element_html' => '<small>…and your WP MySQL password.</small>',
            ]
        );

        $fieldset->addField(
            'dbhost',
            'text',
            [
                'label' => __('Database Host'),
                'title' => __('Database Host'),
                'name' => 'dbhost',
                'required' => true,
                'disabled' => $isElementDisabled,
                'after_element_html' => '<small>…and your WP MySQL host.</small>',
            ]
        );

        $fieldset->addField(
            'prefix',
            'text',
            [
                'label' => __('Table Prefix'),
                'title' => __('Table Prefix'),
                'name' => 'prefix',
                'required' => true,
                'disabled' => $isElementDisabled,
                'after_element_html' => '<small>…and your WP MySQL table prefix.</small>',
            ]
        );

        /**
         * Check is single store mode
         */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'select',
                [
                    'name' => 'store_id',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $this->_systemStore->getStoreValuesForForm(false, false),
                    'disabled' => $isElementDisabled,
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'store_id', 'value' => $this->_storeManager->getStore(true)->getId()]
            );

            $data['store_id'] = $this->_storeManager->getStore(true)->getId();
        }

        $this->_eventManager->dispatch('magetop_blog_import_wordpress_prepare_form', ['form' => $form]);

        /*if (empty($data['homepageurl'])) {
            $data['homepageurl'] = $this->getUrl('blog', ['_store' => 1]);
        }*/

        if (empty($data['prefix'])) {
            $data['prefix'] = 'wp_';
        }

        if (empty($data['dbhost'])) {
            $data['dbhost'] = 'localhost';
        }

        $data['type'] = 'wordpress';

        $form->setValues($data);

        $this->setForm($form);


        return parent::_prepareForm();
    }
}
