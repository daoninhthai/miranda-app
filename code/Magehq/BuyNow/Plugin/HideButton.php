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

namespace Magehq\BuyNow\Plugin;
use Psr\Log\LoggerInterface;
use Magento\Catalog\Model\Product;
class HideButton
{
	private $logger;

	protected $helper;

    public function __construct(\Magehq\BuyNow\Helper\Data $helper, LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->helper = $helper;
    }
    /**
     * Alias for isSalable()
     *
     * @return bool
     */
    public function afterIsSaleable(Product $product)
    {
        return true;
    }
}