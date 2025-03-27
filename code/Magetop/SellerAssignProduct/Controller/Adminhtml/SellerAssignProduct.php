<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Assign_Product
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerAssignProduct\Controller\Adminhtml;

class SellerAssignProduct extends Actions
{
	/**
	 * Form session key
	 * @var string
	 */
    protected $_formSessionKey  = 'magetop_sellerassignproduct_form_data';

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey      = 'Magetop_SellerAssignProduct::sellerassignproduct';

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass      = 'Magetop\SellerAssignProduct\Model\SellerAssignProduct';

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu      = 'Magetop_SellerAssignProduct::sellerassignproduct';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField     = 'status';
}