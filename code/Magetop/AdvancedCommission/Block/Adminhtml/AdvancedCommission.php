<?php
namespace Magetop\AdvancedCommission\Block\Adminhtml;

class AdvancedCommission extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'Magetop_AdvancedCommission';
        $this->_headerText = __('Advanced Commission');
        $this->_addButtonLabel = __('Add Commission');
        parent::_construct();
    }
}