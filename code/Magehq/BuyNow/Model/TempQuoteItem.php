<?php
namespace Magehq\BuyNow\Model;

/**
 * Class TempQuoteItem
 * @package Magehq\BuyNow\Model
 */
class TempQuoteItem extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Magehq\BuyNow\Model\ResourceModel\TempQuoteItem');
    }
}
