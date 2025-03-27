<?php
namespace Magetop\SellerCoupon\Controller\Adminhtml;

class SellerCoupon extends Actions
{
	/**
	 * Form session key
	 * @var string
	 */
    protected $_formSessionKey  = 'magetop_sellercoupon_form_data';

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey      = 'Magetop_SellerCoupon::sellercoupon';

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass      = 'Magetop\SellerCoupon\Model\SellerCoupon';

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu      = 'Magetop_SellerCoupon::sellercoupon';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField     = 'status';
}