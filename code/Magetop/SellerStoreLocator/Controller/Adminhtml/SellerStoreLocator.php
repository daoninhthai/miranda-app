<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Store_Locator
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerStoreLocator\Controller\Adminhtml;

class SellerStoreLocator extends Actions
{
	/**
	 * Form session key
	 * @var string
	 */
    protected $_formSessionKey  = 'magetop_sellerstorelocator_form_data';

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey      = 'Magetop_SellerStoreLocator::sellerstorelocator';

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass      = 'Magetop\SellerStoreLocator\Model\SellerStoreLocator';

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu      = 'Magetop_SellerStoreLocator::sellerstorelocator';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField     = 'status';
}