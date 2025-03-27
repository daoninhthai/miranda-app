<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */

namespace Magetop\Pslider\Block\Adminhtml;


use Magento\Backend\Block\Widget\Grid\Container;

class Grid extends Container{
	protected function _construct()
	{
		$this->_controller = 'adminhtml';
		$this->_blockGroup = 'Magetop_Pslider';
		$this->_headerText = __('Manage Group');
		$this->_addButtonLabel = __('Add New Group');
		parent::_construct();
	}
}