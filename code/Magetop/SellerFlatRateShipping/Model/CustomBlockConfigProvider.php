<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Multiple_Flat_Rate_Shipping
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerFlatRateShipping\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\ScopeInterface;

class CustomBlockConfigProvider implements ConfigProviderInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfiguration;
    protected $_mkProduct;
    protected $_resource;
    protected $_customerFactory;
    protected $_mkShipping;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration,
        \Magetop\Marketplace\Model\ProductsFactory $mkProduct,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magetop\SellerFlatRateShipping\Model\SellerFlatRateShippingFactory $mkShipping
    ) {
        $this->scopeConfiguration = $scopeConfiguration;
        $this->_mkProduct = $mkProduct;
        $this->_resource = $resource;
        $this->_customerFactory = $customerFactory;
        $this->_mkShipping = $mkShipping;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $dataShippingSeller = [];
        $enabled = $this->scopeConfiguration->getValue('carriers/sellerflatrate/active', ScopeInterface::SCOPE_STORE);
        $dataShippingSeller['show_hide_multi_shipping'] = ($enabled)?true:false;
        
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $version = $om->get('Magetop\Marketplace\Helper\Data')->getMagentoVersion();
        $cartData = $om->create('Magento\Checkout\Model\Cart')->getQuote()->getAllVisibleItems();   
        $itemSeller = [];
        $qtyProductSeller = [];   
        $priceProductSeller = [];     
        foreach( $cartData as $item ){
            $mkProductData = $this->_mkProduct->create()->getCollection()
                            ->addFieldToFilter('product_id',$item->getProductId())
                            ->addFieldToFilter('status',1);
            $tableMKuser = $this->_resource->getTableName('multivendor_user');
            $mkProductData->getSelect()->joinLeft(array('mk_user'=>$tableMKuser),'main_table.user_id = mk_user.user_id',array())
                ->where('mk_user.userstatus = 1');
                
            $mkProductData = $mkProductData->getFirstItem();
            if($mkProductData && $mkProductData->getId())
    		{
                //Assign product  
                if(version_compare($version, '2.2.0') >= 0){
                    $infoBuyRequest = $om->get('Magento\Framework\Serialize\Serializer\Json')->unserialize($item->getOptionByCode('info_buyRequest')->getValue());
                }else{
                    $infoBuyRequest = @unserialize($item->getOptionByCode('info_buyRequest')->getValue());
                }
                if(@$infoBuyRequest['assignproduct_id']){
                    $SellerAssignProduct = $om->create('\Magetop\SellerAssignProduct\Model\SellerAssignProduct')->load($infoBuyRequest['assignproduct_id']);
                    $sellerId = $SellerAssignProduct->getSellerId();
                    $itemSeller[$sellerId] = $sellerId;
                    $qtyProductSeller[$sellerId][] = $item->getQty();
                    $priceProductSeller[$sellerId][] =  $item->getBasePrice()*$item->getQty();
                }else{
                    $itemSeller[$mkProductData->getUserId()] = $mkProductData->getUserId();
                    $qtyProductSeller[$mkProductData->getUserId()][] = $item->getQty();
                    $priceProductSeller[$mkProductData->getUserId()][] =  $item->getBasePrice()*$item->getQty();
                }
                //end Assign product
            }
        }
        $priceHelper = $om->create('Magento\Framework\Pricing\Helper\Data');
        foreach( $itemSeller as $key=>$value ){
            $detailShippingSeller = [];  
            $seller = null;
			$useId = $key;
			$tableSellers = $this->_resource->getTableName('multivendor_user');
			$customerModel = $this->_customerFactory->create();
			$sellers = $customerModel->getCollection();
			$sellers->getSelect()->joinLeft(array('table_sellers'=>$tableSellers),'e.entity_id = table_sellers.user_id',array('*'))
				->where('table_sellers.userstatus = 1')
				->where('table_sellers.user_id = ?',$useId);
			$seller = $sellers->getFirstItem();
            
            $shippingData = $this->_mkShipping->create()->getCollection()
                            ->addFieldToFilter('seller_id',$useId)
                            ->addFieldToFilter('status',1);
            $shippingData->setOrder('sort_order', 'ASC' );
            //get total qty product of this seller
            $qty = 0;
            foreach($qtyProductSeller[$key] as $val){
                $qty += $val;
            }
            //get total price product of this seller
            $price = 0;
            foreach($priceProductSeller[$key] as $val){
                $price += $val;
            }
            foreach($shippingData as $sp){
                //get price shipping by type (per order/per item)
                if($sp->getType() == 2){
                    $price_sp = $qty*$sp->getPrice();
                }else{
                    $price_sp = $sp->getPrice();
                }
                //get free shipping
                if($sp->getFreeShipping() == 0){
                    $price_shipping = $price_sp;
                }else{
                    if($price >= $sp->getFreeShipping()){
                        $price_shipping = 0;
                    }else{
                        $price_shipping = $price_sp;
                    }
                }
                $detailShippingSeller[] = array(
                    'id'=>'id_'.$sp->getId().'',
                    'name'=>'sellerflatrate['.$useId.']',
                    'value'=>$price_shipping,
                    'price'=>$priceHelper->currency($price_shipping, true, false),
                    'title'=>$sp->getTitle()
                );
            }
            $dataShippingSeller['seller_flat_rate'][] = array(
                'seller_name'=>'Seller : '.$seller->getStoretitle().'', 
                'seller_id'=>$useId, 
                'input_name'=>'sellerflatrate['.$useId.']', 
                'detail'=>$detailShippingSeller
            );
    	}
        return $dataShippingSeller;
    }
}