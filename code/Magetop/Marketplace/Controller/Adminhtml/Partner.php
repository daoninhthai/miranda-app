<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Controller\Adminhtml;

class Partner extends Partneractions
{
	/**
	 * Form session key
	 * @var string
	 */
    protected $_formSessionKey  = 'marketplace_partner_form_data';

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey      = 'Magetop_Marketplace::manage_partner';

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass      = 'Magetop\Marketplace\Model\Partner';

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu      = 'Magetop_Marketplace::manage_partner';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField     = 'status';
}