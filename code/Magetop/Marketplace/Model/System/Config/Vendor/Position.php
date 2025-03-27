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
 
class Position implements ArrayInterface
{
    /**
     * Get positions of lastest news block
     *
     * @return array
     */
    public function toOptionArray()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $setOptionArray = $objectManager->create('Magetop\Marketplace\Helper\Data')->getOptionSetGroup();
        
        $data = array();
        
        foreach($setOptionArray as $k=>$v){

            $data[] = array('label' => $v['label'], 'value' => strtolower($v['value']));
        }

        return $data;
    }
}