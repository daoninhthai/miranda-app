<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Membership
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerMembership\Controller\Adminhtml;

class SellerMembership extends SellerMembershipActions
{
	/**
	 * Form session key
	 * @var string
	 */
    protected $_formSessionKey  = 'magetop_sellermembership_form_data';

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey      = 'Magetop_SellerMembership::sellermembership';

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass      = 'Magetop\SellerMembership\Model\SellerMembership';

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu      = 'Magetop_SellerMembership::sellermembership';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField     = 'status';
}