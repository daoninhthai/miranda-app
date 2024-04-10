<?php
namespace Magetop\AdvancedCommission\Controller\Adminhtml;

class AdvancedCommission extends Actions
{
	/**
	 * Form session key
	 * @var string
	 */
    protected $_formSessionKey  = 'magetop_advancedcommission_form_data';

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey      = 'Magetop_AdvancedCommission::advancedcommission';

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass      = 'Magetop\AdvancedCommission\Model\AdvancedCommission';

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu      = 'Magetop_AdvancedCommission::advancedcommission';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField     = 'is_active';
}