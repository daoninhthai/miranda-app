<?php
namespace Magetop\AdvancedCommission\Block\Adminhtml\AdvancedCommission\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('advancedcommission_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Commission Information'));
    }
}