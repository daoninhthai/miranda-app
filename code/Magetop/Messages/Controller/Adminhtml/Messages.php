<?php
namespace Magetop\Messages\Controller\Adminhtml;

class Messages extends Actions
{
	/**
	 * Form session key
	 * @var string
	 */
    protected $_formSessionKey  = 'magetop_messages_form_data';

    /**
     * Allowed Key
     * @var string
     */
    protected $_allowedKey      = 'Magetop_Messages::messages';

    /**
     * Model class name
     * @var string
     */
    protected $_modelClass      = 'Magetop\Messages\Model\Messages';

    /**
     * Active menu key
     * @var string
     */
    protected $_activeMenu      = 'Magetop_Messages::messages';

    /**
     * Status field name
     * @var string
     */
    protected $_statusField     = 'is_active';
}