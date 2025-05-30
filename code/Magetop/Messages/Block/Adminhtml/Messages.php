<?php
namespace Magetop\Messages\Block\Adminhtml;

class Messages extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml';
        $this->_blockGroup = 'Magetop_Messages';
        $this->_headerText = __('Messages');
        $this->_addButtonLabel = __('Add Message');
        parent::_construct();
    }
}