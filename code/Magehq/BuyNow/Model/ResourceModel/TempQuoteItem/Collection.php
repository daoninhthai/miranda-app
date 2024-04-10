<?php
/**
 * Magehq
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magehq.com license that is
 * available through the world-wide-web at this URL:
 * https://magehq.com/license.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Magehq
 * @package    Magehq_BuyNow
 * @copyright  Magehq\Copyright (c) 2022 Magehq (https://magehq.com/)
 * @license    https://magehq.com/license.html
 */
 
namespace Magehq\BuyNow\Model\ResourceModel\TempQuoteItem;

/**
 * Class Collection
 * @package Magehq\BuyNow\Model\ResourceModel\TempQuoteItem
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    protected function _construct()
    {
        $this->_setIdFieldName('entity_id');
        $this->_init('Magehq\BuyNow\Model\TempQuoteItem', 'Magehq\BuyNow\Model\ResourceModel\TempQuoteItem');
    }
}
