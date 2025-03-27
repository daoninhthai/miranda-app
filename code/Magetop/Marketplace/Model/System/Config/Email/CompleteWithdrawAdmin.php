<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Model\System\Config\Email;
 
use Magento\Framework\Option\ArrayInterface;
 
class CompleteWithdrawAdmin implements ArrayInterface
{
    /**
     * Get all product type
     *
     * @return array
     */
    public function toOptionArray()
    {
        $types = array('Email Complete Withdraw Admin (Default)'=>'marketplace_general_email_complete_withdraw_admin');
        $data = array();

        foreach($types as $label => $value)	{
            $data[] = array('label' => $label, 'value' => strtolower($value));
        }

        return $data;
    }
}