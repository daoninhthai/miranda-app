<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Menupro\Block\Adminhtml;

/**
 * Adminhtml custom blocks content block
 */
class Menu extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_blockGroup = 'Magetop_Menu';
        $this->_controller = 'adminhtml_custom';
        $this->_headerText = __('Menupro Module');
        $this->_addButtonLabel = __('Add New');
        
        parent::_construct();
    }
}
