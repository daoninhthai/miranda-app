<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */

namespace Magetop\Menupro\Block\System\Config\Form;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magetop\Menupro\Model\Menupro;

class Button extends Field
{
    /**
     * @var string
     */
    protected $_template = 'Magetop_Menupro::menupro/system/config/button.phtml';

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
		Menupro $menuproModel,
        array $data = []
    ) {
        parent::__construct($context, $data);
		$this->_menuproModel = $menuproModel;
    }

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for collect button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->_menuproModel->getBaseUrl().'menupro/index/refresh';
    }

    /**
     * Generate collect button html
     *
     * @return string
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'refresh_button',
                'label' => __('Refresh'),
            ]
        );

        return $button->toHtml();
    }
}