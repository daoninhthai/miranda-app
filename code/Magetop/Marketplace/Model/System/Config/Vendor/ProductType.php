<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Model\System\Config\Vendor;
 
use Magento\Framework\Option\ArrayInterface;
 
class ProductType implements ArrayInterface
{
    /**
     * Get all product type
     *
     * @return array
     */
    public function toOptionArray()
    {
        $types = array('Simple', 'Download', 'Virtual', 'Configurable', 'Grouped', 'Bundle');
        $data = array();

        foreach($types as $type)	{
            $data[] = array('label' => $type, 'value' => strtolower($type));
        }

        return $data;
    }
}