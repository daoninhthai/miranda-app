<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Product_Attribute_Management
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\ProductAttributeManagement\Controller\Adminhtml;

class ProductAttributeManagement extends Actions
{
	/**
	 * Form session key
	 * @var string
	 */
    protected $_formSessionKey  = 'magetop_productattributemanagement_form_data';

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey      = 'Magetop_ProductAttributeManagement::productattributemanagement';

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass      = 'Magetop\ProductAttributeManagement\Model\ProductAttributeManagement';

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu      = 'Magetop_ProductAttributeManagement::productattributemanagement';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField     = 'status';
}