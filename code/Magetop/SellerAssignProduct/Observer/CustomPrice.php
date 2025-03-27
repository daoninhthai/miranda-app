<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Assign_Product
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/

namespace Magetop\SellerAssignProduct\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class CustomPrice implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $version = $objectManager->get('Magetop\Marketplace\Helper\Data')->getMagentoVersion();
        $item = $observer->getEvent()->getData('quote_item');
        $product = $observer->getEvent()->getData('product');
        $item = ($item->getParentItem() ? $item->getParentItem() : $item );
        if($item->getOptionByCode('additional_options')){
            if(version_compare($version, '2.2.0') >= 0){
                $data = $objectManager->get('Magento\Framework\Serialize\Serializer\Json')->unserialize($item->getOptionByCode('additional_options')->getValue());
            }else{
                $data = @unserialize($item->getOptionByCode('additional_options')->getValue());
            }
            if(@$data[0]['price']){
                // Load the custom price
                $price = $data[0]['price'];
                // Set the custom price
                $item->setCustomPrice($price);
                $item->setOriginalCustomPrice($price);
                // Enable super mode on the product.
                $item->getProduct()->setIsSuperMode(true);
            }
        }
        return $this;
    }
}