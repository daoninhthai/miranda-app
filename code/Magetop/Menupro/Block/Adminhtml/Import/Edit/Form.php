<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2013-05-5
 * @@Modify Date: 2017-06-05
 */
/*--------------------*/
namespace Magetop\Menupro\Block\Adminhtml\Import\Edit;

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
     * @var \Magetop\Menupro\Model\Import\Groupmenu
     */
    protected $groupmenu;
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
        \Magetop\Menupro\Model\Import\Groupmenu $groupmenu,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
        $this->_yesno = $yesno;

        $this->_systemStore = $systemStore;
        $this->_groupmenu = $groupmenu;
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

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Groupmenu Id')]);
        $field = $fieldset->addField(
            'groupmenu_id',
            'select',
            [
                'name' => 'groupmenu_id',
                'label' => __('Groupmenu Id'),
                'title' => __('Groupmenu Id'),
                'required' => true,
                'values' => $this->_groupmenu->toOptionArray()
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

