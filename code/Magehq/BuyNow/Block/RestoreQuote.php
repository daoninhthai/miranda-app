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
namespace Magehq\BuyNow\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\ObjectManagerInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magehq\BuyNow\Model\TempQuoteItemFactory;

/**
 * Class RestoreQuote
 * @package Magehq\BuyNow\Block
 */
class RestoreQuote extends \Magento\Framework\View\Element\Template {

    private $cart;

    private $tempQuoteItemCollectionFactory;

    private $objectManager;

    private $productRepository;

    private $checkoutSession;

    protected $orderRepository;

    /**
     * RestoreQuote constructor.
     * @param Template\Context $context
     * @param array $data
     * @param ObjectManagerInterface $objectManager
     * @param ProductRepositoryInterface $productRepository
     * @param CustomerCart $cart
     * @param TempQuoteItemFactory $tempQuoteItemCollectionFactory
     */
    public function __construct(
        Template\Context $context, 
        ObjectManagerInterface $objectManager,
        ProductRepositoryInterface $productRepository,
        CustomerCart $cart,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        TempQuoteItemFactory $tempQuoteItemCollectionFactory,
        array $data = []
    ){
        $this->orderRepository = $orderRepository;
        $this->objectManager = $objectManager;
        $this->productRepository = $productRepository;
        $this->cart = $cart;
        $this->checkoutSession = $checkoutSession;
        $this->tempQuoteItemCollectionFactory = $tempQuoteItemCollectionFactory;
        parent::__construct($context, $data);
    }

    public function getOrder($id)
    {
        return $this->orderRepository->get($id);
    }

    public function _construct()
    {
        $currentUrl = $this->getUrl('*/*/*', ['_forced_secure' => true, '_use_rewrite' => true]);
        $ignoreUrls = [
            $this->getUrl('checkout'),
            $this->getUrl('checkout/index'),
            $this->getUrl('checkout/index/index'),
            $this->getUrl('onestepcheckout'),
            $this->getUrl('onestepcheckout/index'),
            $this->getUrl('onestepcheckout/index/index'),
            $this->getUrl('checkout/cart/add'),
            $this->getUrl('customer/section/load'),
        ];
        if(!in_array($currentUrl, $ignoreUrls)){
           if($currentUrl == $this->getUrl('checkout/onepage/success')) {
                $orderId = $this->checkoutSession->getData('last_order_id');
                $order = $this->getOrder($orderId);
                $quoteId = $order->getQuoteId();
            } else {
                $quoteId = $this->cart->getQuote()->getId();
            }
            $tempItemCollection = $this->tempQuoteItemCollectionFactory->create()->getCollection()
                ->addFieldToFilter('quote_id', ['eq' => $quoteId]);

            if(count($tempItemCollection)){
                foreach ($tempItemCollection as $item) {
                    $infoBuyRequest = json_decode($item->getInfoBuyRequest(), true);
                    
                    $infoBuyRequest['qty'] = $infoBuyRequest['original_qty'];


                    if(isset($item['product_id'])) {
                        $productId = $item['product_id'];


                        if(isset($infoBuyRequest['super_attribute'])) {
                            $super_attribute = $infoBuyRequest['super_attribute'];
                            if(isset($infoBuyRequest['product'])) {
                                $productId = $infoBuyRequest['product'];
                            }
                        } else {
                            $super_attribute = '';
                        }

                        $product = $this->productRepository->getById ($productId);

                        $related = isset($infoBuyRequest['related_product']) ? $infoBuyRequest['related_product'] : "";
                        unset($infoBuyRequest['original_qty']);

                        $this->cart->addProduct($product, $infoBuyRequest);
                        if (!empty($related)) {
                            $this->cart->addProductsByIds(explode(',', $related));
                        }
                        
                        $item->delete();
                    }
                }
                $this->cart->save();
            }

           

        }

        parent::_construct();
    }

    /**
     * @param $productId
     * @return bool|\Magento\Catalog\Api\Data\ProductInterface
     */
    protected function _initProduct($productId)
    {
        if ($productId) {
            $storeId = $this->objectManager->get(
                \Magento\Store\Model\StoreManagerInterface::class
            )->getStore()->getId();
            try {
                return $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }
}