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
 
class UnapproveVendor implements ArrayInterface
{
    /**
     * Get all product type
     *
     * @return array
     */
    public function toOptionArray()
    {
        $types = array('Email Unapprove Vendor (Default)'=>'marketplace_general_email_unapprove_vendor');
        $data = array();

        foreach($types as $label => $value)	{
            $data[] = array('label' => $label, 'value' => strtolower($value));
        }

        return $data;
    }
}