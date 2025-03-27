<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Vacation
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerVacation\Controller\Adminhtml;

class SellerVacation extends Actions
{
	/**
	 * Form session key
	 * @var string
	 */
    protected $_formSessionKey  = 'magetop_sellervacation_form_data';

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey      = 'Magetop_SellerVacation::sellervacation';

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass      = 'Magetop\SellerVacation\Model\SellerVacation';

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu      = 'Magetop_SellerVacation::sellervacation';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField     = 'status';
}