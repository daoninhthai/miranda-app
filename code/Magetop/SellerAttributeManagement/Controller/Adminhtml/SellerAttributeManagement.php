<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Attribute_Management
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerAttributeManagement\Controller\Adminhtml;

class SellerAttributeManagement extends Actions
{
	/**
	 * Form session key
	 * @var string
	 */
    protected $_formSessionKey  = 'magetop_sellerattributemanagement_form_data';

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey      = 'Magetop_SellerAttributeManagement::sellerattributemanagement';

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass      = 'Magetop\SellerAttributeManagement\Model\SellerAttributeManagement';

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu      = 'Magetop_SellerAttributeManagement::sellerattributemanagement';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField     = 'status';
}