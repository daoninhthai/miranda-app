<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Store_Pickup
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerStorePickup\Controller\Adminhtml;

class SellerStorePickup extends Actions
{
	/**
	 * Form session key
	 * @var string
	 */
    protected $_formSessionKey  = 'magetop_sellerstorepickup_form_data';

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey      = 'Magetop_SellerStorePickup::sellerstorepickup';

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass      = 'Magetop\SellerStorePickup\Model\SellerStorePickup';

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu      = 'Magetop_SellerStorePickup::sellerstorepickup';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField     = 'status';
}