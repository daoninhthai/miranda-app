<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://Magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */

namespace Magetop\Themes\Block\Adminhtml\Import\Edit;

use Magento\Theme\Model\Theme\Collection;
use Magento\Framework\App\Area;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $_yesno;
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;
    /**
     * @var \Magetop\Themes\Model\Import\Theme
     */
    protected $_theme;
    /**
     * @param \Magento\Backend\Block\Template\Context                    $context       
     * @param \Magento\Framework\Registry                                $registry      
     * @param \Magento\Framework\Data\FormFactory                        $formFactory   
     * @param \Magento\Config\Model\Config\Source\Yesno                  $yesno               
     * @param \Magento\Store\Model\System\Store                          $systemStore   
     * @param array                                                      $data          
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Config\Model\Config\Source\Yesno $yesno,
        \Magento\Store\Model\System\Store $systemStore,
        \Magetop\Themes\Model\Import\Theme $theme,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_yesno = $yesno;

        $this->_systemStore = $systemStore;
        $this->_theme = $theme;
    }
    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
                [
                    'data' => [
                    'id' => 'edit_form',
                    'action' => $this->getData('action'),
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                    ]
                ]
            );

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Select Theme')]);
        $field = $fieldset->addField(
            'theme_path',
            'select',
            [
                'name' => 'theme_path',
                'label' => __('Theme'),
                'title' => __('Theme'),
                'required' => true,
                'values' => $this->_theme->toOptionArray()
            ]
        );
        $scope    = $this->getRequest()->getParam('store');
        if($scope){
            $scopeId = $this->_storeManager->getStore($scope)->getId();
            $fieldset->addField('scope', 'hidden', array(
                'label'     => __('Scope'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'scope',
                'value'     => 'stores',
            ));
            $fieldset->addField('scope_id', 'hidden', array(
                'label'     => __('Scope Id'),
                'class'     => 'required-entry',
                'required'  => true,
                'name'      => 'scope_id',
                'value'     => $scopeId,
            ));
        }else {
            $scope   = $this->getRequest()->getParam('website');
            if($scope){
                $scopeId = $this->_storeManager->getWebsite($scope)->getId();
                $fieldset->addField('scope', 'hidden', array(
                    'label'     => __('Scope'),
                    'class'     => 'required-entry',
                    'required'  => true,
                    'name'      => 'scope',
                    'value'     => 'websites',
                ));
                $fieldset->addField('scope_id', 'hidden', array(
                    'label'     => __('Scope Id'),
                    'class'     => 'required-entry',
                    'required'  => true,
                    'name'      => 'scope_id',
                    'value'     => $scopeId,
                ));             
            }

        }
        $fieldset->addField('config', 'checkbox',
            [
                'label' => __('Config'),
                'title' => __('Config'),
                'name' => 'config',
                'value' => 1,
                'checked' => 'checked',
                'after_element_html' => '<small> STORES > Configuration</small>',
            ]
        );

        $fieldset->addField('page', 'checkbox',
            [
                'label' => __('Pages'),
                'title' => __('Pages'),
                'name' => 'page',
                'value' => 1,
                'checked' => 'checked',
                'after_element_html' => '<small> CONTENT > Pages</small>',
            ]
        );

        $overwrite_block = $fieldset->addField('overwrite_page', 'checkbox',
            [
                'label' => __(''),
                'title' => __('Overwrite Existing Pages'),
                'name' => 'overwrite_page',
                'value' => 1,
                'checked' => 'checked',
                'after_element_html' => '<small> Overwrite Existing Pages</small>',
            ]
        );

        $block = $fieldset->addField('block', 'checkbox',
            [
                'label' => __('Blocks'),
                'title' => __('Blocks'),
                'name' => 'block',
                'value' => 1,
                'checked' => 'checked',
                'after_element_html' => '<small> CONTENT > Blocks</small>',
            ]
        );


        $overwrite_block = $fieldset->addField('overwrite_block', 'checkbox',
            [
                'label' => __(''),
                'title' => __('Overwrite Existing Blocks'),
                'name' => 'overwrite_block',
                'value' => 1,
                'checked' => 'checked',
                'after_element_html' => '<small> Overwrite Existing Blocks</small>',
            ]
        );

        $block = $fieldset->addField('action', 'select',
            [
                'label' => __('Action'),
                'title' => __('Action'),
                'name' => 'action',
                'values' =>  array(
                    array('value' => 1, 'label' => __('Install')),
                    array('value' => 0, 'label' => __('Uninstall')),
                ),
                'value' => 1,
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}

