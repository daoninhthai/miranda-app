<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;

class salesConvertQuoteItemToOrderItem implements ObserverInterface
{
    public function execute(EventObserver $observer)
    {
		$quoteItem = $observer->getItem();
        if ($additionalOptions = $quoteItem->getOptionByCode('additional_options')) {
            $orderItem = $observer->getOrderItem();
            $options = $orderItem->getProductOptions();
            $om = \Magento\Framework\App\ObjectManager::getInstance();
            $version = $om->get('Magetop\Marketplace\Helper\Data')->getMagentoVersion();
            if(version_compare($version, '2.2.0') >= 0){
                $options['additional_options'] = $om->get('Magento\Framework\Serialize\Serializer\Json')->unserialize($additionalOptions->getValue());
            }else{
                $options['additional_options'] = @unserialize($additionalOptions->getValue());
            }
            $orderItem->setProductOptions($options);
        }
    }
}
