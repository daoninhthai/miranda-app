<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Shipping
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerShipping\Model\Checkout;

use Magento\Quote\Api\CartRepositoryInterface;

class ShippingInformationManagementPlugin
{
    /**
    * @var CartRepositoryInterface
    */
    private $cartRepository;
    protected $quoteRepository;

    public function __construct(
        CartRepositoryInterface $cartRepository,
        \Magento\Quote\Model\QuoteRepository $quoteRepository
    ) {
        $this->cartRepository = $cartRepository;
        $this->quoteRepository = $quoteRepository;
    }

    /**
    * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
    * @param $cartId
    * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        $quote = $this->cartRepository->getActive($cartId);
        $extAttributes = $addressInformation->getShippingAddress()->getExtensionAttributes();
        
        $sellerFlatRate = @$extAttributes->getSellerFlatRateShipping();
        $sellerTableRate = @$extAttributes->getSellerTableRateShipping();
        $sellerStorePickup = @$extAttributes->getSellerStorePickupShipping();
        
        if(@$sellerFlatRate){
            $data = explode('split',$sellerFlatRate??'');
            \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Catalog\Model\Session')->setSellerFlatRateShippingPrice($data[0]);
            $quote->setData('seller_flat_rate_shipping', substr($data[1],0,-1));
        }
        if(@$sellerTableRate){
            $data = explode('split',$sellerTableRate??'');
            \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Catalog\Model\Session')->setSellerTableRateShippingPrice($data[0]);
            $quote->setData('seller_table_rate_shipping', substr($data[1],0,-1));
        }
        if(@$sellerStorePickup){
            $data = explode('split',$sellerStorePickup??'');
            \Magento\Framework\App\ObjectManager::getInstance()->create('\Magento\Catalog\Model\Session')->setSellerStorePickupShippingPrice($data[0]);
            $quote->setData('seller_store_pickup_shipping', substr($data[1],0,-1));
        }
        
        return [$cartId, $addressInformation];
    }
}