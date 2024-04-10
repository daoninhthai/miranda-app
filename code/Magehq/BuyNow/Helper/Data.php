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

namespace Magehq\BuyNow\Helper;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Catalog\Api\ProductRepositoryInterface;
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
     /**
     * Buynow button title path
     */
    const BUYNOW_BUTTON_TITLE_PATH = 'buynow/general/button_title';

    /**
     * Buynow button title
     */
    const BUYNOW_BUTTON_TITLE = 'Buy Now';

    /**
     * Addtocart button form id path
     */
    const ADDTOCART_FORM_ID_PATH = 'buynow/general/addtocart_id';

    /**
     * Addtocart button form id
     */
    const ADDTOCART_FORM_ID = 'product_addtocart_form';

    /**
     * Keep cart products path
     */
    const KEEP_CART_PRODUCTS_PATH = 'buynow/general/keep_cart_products';

    /**
     * @var \Magento\Framework\Url\Helper\Data
     */
    protected $urlHelper;

    protected $productRepository;
    protected $orderRepository;
    protected $cart;
    protected $url;
    protected $checkoutSession;
    protected $tempQuoteItemCollectionFactory;
    protected $_scopeConfig;

    public function __construct
    (
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        ProductRepositoryInterface $productRepository,
        \Magehq\BuyNow\Model\TempQuoteItemFactory $tempQuoteItemCollectionFactory,
        CustomerCart $cart,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        \Magento\Framework\App\Request\Http $request
    ) {
         parent::__construct($context);
         $this->urlHelper = $urlHelper;
         $this->productRepository = $productRepository;
         $this->orderRepository = $orderRepository;
        $this->_request = $request;
        $this->cart = $cart;
        $this->url = $context->getUrlBuilder();
        $this->checkoutSession = $checkoutSession;
         $this->tempQuoteItemCollectionFactory = $tempQuoteItemCollectionFactory;
        $this->_scopeConfig = $context->getScopeConfig();
    }

    public function getCategoryTemplate()
    {
        if ($this->getEnabled() && $this->getConfig('buynow/general/enable_list')) {
            $template =  'Magehq_BuyNow::product/list.phtml';
        } else {
            $template = 'Magento_Catalog::product/list.phtml';
        }
 
        return $template;
    }

    public function getSearchTemplate()
    {
        if ($this->getEnabled() && $this->getConfig('buynow/general/enable_search')) {
            $template =  'Magehq_BuyNow::product/list.phtml';
        } else {
            $template = 'Magento_Catalog::product/list.phtml';
        }
 
        return $template;
    }

    public function getAdvancedTemplate()
    {
        if ($this->getEnabled() && $this->getConfig('buynow/general/enable_advanced_search')) {
            $template =  'Magehq_BuyNow::product/list.phtml';
        } else {
            $template = 'Magento_Catalog::product/list.phtml';
        }
 
        return $template;
    }

    public function restoreQuote($currentUrl) {
         
        $ignoreUrls = [
            $this->url->getUrl('checkout'),
            $this->url->getUrl('checkout/index'),
            $this->url->getUrl('checkout/index/index'),
            $this->url->getUrl('onestepcheckout'),
            $this->url->getUrl('onestepcheckout/index'),
            $this->url->getUrl('onestepcheckout/index/index'),
            $this->url->getUrl('checkout/cart/add'),
            $this->url->getUrl('customer/section/load'),
        ];

       // return $currentUrl;

        if(!in_array($currentUrl, $ignoreUrls)){
            if($currentUrl.'/' == $this->url->getUrl('checkout/onepage/success')) {
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
                         

                        $buyRequest = new \Magento\Framework\DataObject([
                         'qty' => $infoBuyRequest['qty'],
                           'super_attribute' => $super_attribute
                       ]);
                        
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
         return true;
    }

    public function getOrder($id)
    {
        return $this->orderRepository->get($id);
    }
    /**
     * Retrieve config value
     *
     * @return string
     */
    public function getConfig($config)
    {
        return $this->scopeConfig->getValue(
            $config,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Enable module
     *
     * @return string
     */
    public function getEnabled()
    {
        return $this->scopeConfig->getValue(
            'buynow/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }


    /**
     * Get button title
     * @return string
     */
    public function getButtonTitle()
    {
        $btnTitle = $this->getConfig(self::BUYNOW_BUTTON_TITLE_PATH);
        return $btnTitle ? $btnTitle : self::BUYNOW_BUTTON_TITLE;
    }

    /**
     * Get addtocart form id
     * @return string
     */
    public function getAddToCartFormId()
    {
        $addToCartFormId = $this->getConfig(self::ADDTOCART_FORM_ID_PATH);
        return $addToCartFormId ? $addToCartFormId : self::ADDTOCART_FORM_ID;
    }

    /**
     * Check if keep cart products
     * @return string
     */
    public function keepCartProducts()
    {
        return $this->getConfig(self::KEEP_CART_PRODUCTS_PATH);
    }
     /**
     * Restore cart products
     * @return string
     */
    public function restoreCartProducts()
    {
        return $this->getConfig('buynow/general/restore_cart_products');
    }
}
