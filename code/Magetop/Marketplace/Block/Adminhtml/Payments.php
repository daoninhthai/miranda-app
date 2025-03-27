<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
 
namespace Magetop\Marketplace\Block\Adminhtml;
use Magento\Backend\Block\Widget\Grid\Container;

class Payments extends Container
{
   /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_payments';
        $this->_blockGroup = 'Magetop_Marketplace';
        $this->_headerText = __('Manage Payment Method');
        $this->_addButtonLabel = __('Add New Payment Method');
        parent::_construct();
    }
}