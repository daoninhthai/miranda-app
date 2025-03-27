<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://Magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Themes\Block\System\Config\Form\Field;

/**
 * Backend system config array field renderer
 */
class Color extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $_elementFactory;
    /**
     * @var \Magento\Framework\View\Design\Theme\LabelFactory
     */
    protected $_developer;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Data\Form\Element\Factory $elementFactory
     * @param \Magento\Framework\View\Design\Theme\LabelFactory $labelFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        array $data = []
    ) {
        $this->_elementFactory = $elementFactory;
        parent::__construct($context, $data);
    }
    /**
     * Initialise form fields
     *
     * @return void
     */
    protected function _construct()
    {
        $this->addColumn('selector', array(
            'label' => __('Selector'),
            'class' => 'selector',
        ));
        $this->addColumn('color', array(
            'label' => __('Color'),
            'style' => 'width:116px',
            'class' =>   $this->classColor(),
        ));  
        $this->addColumn('background', array(
            'label' => __('background-color'),
            'style' => 'width:116px',
            'class' =>  $this->classColor(),
        )); 
        $this->addColumn('border', array(
            'label' => __('border-color'),
            'style' => 'width:116px',
            'class' =>  $this->classColor(),
        )); 
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add \Config Color');
        parent::_construct();
    }
    public function classColor()
    {
        return 'mb-color';
    }
    public function addColumn($name, $params)
    {
       $label = ($name != 'selector') ? $params['label'] : '';
        $this->_columns[$name] = array(
            'label'     => $label,
            'size'      => empty($params['size'])  ? false    : $params['size'],
            'style'     => empty($params['style'])  ? null    : $params['style'],
            'class'     => empty($params['class'])  ? null    : $params['class'],
            'renderer'  => false,
        );
        if ((!empty($params['renderer'])) && ($params['renderer'] instanceof \Magento\Framework\View\Element\AbstractBlock)) {
            $this->_columns[$name]['renderer'] = $params['renderer'];
        }
    }
}
