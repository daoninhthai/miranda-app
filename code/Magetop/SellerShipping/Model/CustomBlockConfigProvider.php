<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Shipping
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerShipping\Model;

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

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration
     * @codeCoverageIgnore
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfiguration,
        \Magetop\Marketplace\Model\ProductsFactory $mkProduct,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Customer\Model\CustomerFactory $customerFactory
    ) {
        $this->scopeConfiguration = $scopeConfiguration;
        $this->_mkProduct = $mkProduct;
        $this->_resource = $resource;
        $this->_customerFactory = $customerFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $dataShippingSeller = [];
        
        // seller flat rate shipping
        $enable_seller_flat_rate = $this->scopeConfiguration->getValue('carriers/sellerflatrate/active', ScopeInterface::SCOPE_STORE);
        $dataShippingSeller['enable_seller_flat_rate'] = ($enable_seller_flat_rate)?true:false;
        
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
                
            $mkProductData = $mkProductData->getData();
            if($mkProductData && $mkProductData[0]['id'])
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
                    $itemSeller[$mkProductData[0]['user_id']] = $mkProductData[0]['user_id'];
                    $qtyProductSeller[$mkProductData[0]['user_id']][] = $item->getQty();
                    $priceProductSeller[$mkProductData[0]['user_id']][] =  $item->getBasePrice()*$item->getQty();
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
            
            $shippingData = $om->create('Magetop\SellerFlatRateShipping\Model\SellerFlatRateShipping')->getCollection()
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
                    'id'=>'flat_'.$sp->getId().'',
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
        
        // seller table rate shipping
        $enable_seller_table_rate = $this->scopeConfiguration->getValue('carriers/sellertablerate/active', ScopeInterface::SCOPE_STORE);
        $dataShippingSeller['enable_seller_table_rate'] = ($enable_seller_table_rate)?true:false;
        
        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $version = $om->get('Magetop\Marketplace\Helper\Data')->getMagentoVersion();
        $cartData = $om->create('Magento\Checkout\Model\Cart')->getQuote()->getAllVisibleItems();    
        $itemSeller = [];
        $qtyProductSeller = [];   
        $priceProductSeller = []; 
        $productNotWeight = array('booking','virtual','downloadable'); 
        $weightProductSeller = [];   
        foreach( $cartData as $item ){
            if(!in_array($item->getProduct()->getTypeId(),$productNotWeight)){
                $mkProductData = $this->_mkProduct->create()->getCollection()
                                ->addFieldToFilter('product_id',$item->getProductId())
                                ->addFieldToFilter('status',1);
                $tableMKuser = $this->_resource->getTableName('multivendor_user');
                $mkProductData->getSelect()->joinLeft(array('mk_user'=>$tableMKuser),'main_table.user_id = mk_user.user_id',array())
                    ->where('mk_user.userstatus = 1');
                    
                $mkProductData = $mkProductData->getData();
                if($mkProductData && $mkProductData[0]['id'])
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
                        $weightProductSeller[$sellerId][] =  $item->getWeight()*$item->getQty();
                    }else{
                        $itemSeller[$mkProductData[0]['user_id']] = $mkProductData[0]['user_id'];
                        $qtyProductSeller[$mkProductData[0]['user_id']][] = $item->getQty();
                        $priceProductSeller[$mkProductData[0]['user_id']][] =  $item->getBasePrice()*$item->getQty();
                        $weightProductSeller[$mkProductData[0]['user_id']][] =  $item->getWeight()*$item->getQty();
                    }
                    //end Assign product
                }
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
            
            $shippingData = $om->create('Magetop\SellerTableRateShipping\Model\SellerTableRateShipping')->getCollection()
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
            //get total weight product of this seller
            $weight = 0;
            foreach($weightProductSeller[$key] as $val){
                $weight += $val;
            }
            foreach($shippingData as $sp){
                if(($sp->getWeightFrom() <= $weight) && ($sp->getWeightTo() >= $weight)){
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
                        'id'=>'table_'.$sp->getId().'',
                        'name'=>'sellertablerate['.$useId.']',
                        'value'=>$price_shipping,
                        'price'=>$priceHelper->currency($price_shipping, true, false),
                        'title'=>$sp->getTitle(),
                        'country_code'=>$sp->getCountryCode(),
                        'region_id'=>$sp->getRegionId(),
                        'zip_from'=>$sp->getZipFrom(),
                        'zip_to'=>$sp->getZipTo(),
                        'weight_from'=>$sp->getWeightFrom(),
                        'weight_to'=>$sp->getWeightTo()
                    );
                }
            }
            $dataShippingSeller['seller_table_rate'][] = array(
                'seller_name'=>__('Seller').' : '.$seller->getStoretitle().'', 
                'seller_id'=>$useId, 
                'input_name'=>'sellertablerate['.$useId.']', 
                'total_weight'=>$weight,
                'detail'=>$detailShippingSeller
            );
    	}
        
        // seller store pickup shipping
        $enable_seller_store_pickup = $this->scopeConfiguration->getValue('carriers/sellerstorepickup/active', ScopeInterface::SCOPE_STORE);
        $dataShippingSeller['enable_seller_store_pickup'] = ($enable_seller_store_pickup)?true:false;
        
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
                
            $mkProductData = $mkProductData->getData();
            if($mkProductData && $mkProductData[0]['id'])
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
                    $itemSeller[$mkProductData[0]['user_id']] = $mkProductData[0]['user_id'];
                    $qtyProductSeller[$mkProductData[0]['user_id']][] = $item->getQty();
                    $priceProductSeller[$mkProductData[0]['user_id']][] =  $item->getBasePrice()*$item->getQty();
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
            
            $shippingData = $om->create('Magetop\SellerStorePickup\Model\SellerStorePickup')->getCollection()
                            ->addFieldToFilter('seller_id',$useId)
                            ->addFieldToFilter('status',1);
            $shippingData->setOrder('id', 'ASC' );

            foreach($shippingData as $sp){
                $detail_store = '<legend class="legend">
                                    <span>
                                        Store address                
                                    </span>
                                </legend>';
                $detail_store .= 'Stress Address:'.$sp->getAddress().'<br>'.
                                'City:'.$sp->getCity().'<br>'.
                                'State/Province:'.$sp->getState().'<br>'.
                                'Zip/Postal Code:'.$sp->getZipcode().'<br>'.
                                'Country:'.$sp->getCountry().'<br>';
                                
                $store_time = json_decode($sp->getStoreTime());
                $time_store = '<legend class="legend">
                                    <span>
                                        Store Times (In 24 Hour Time Format)              
                                    </span>
                                </legend>'; 
                for($day = 2;$day<=8;$day++){
                    switch($day){
                        case 2 : 
                            $time_store .= __('Monday : ');
                            $open_day = $store_time->mon_in_time;
                            $close_day = $store_time->mon_out_time;
                            $time_store .= __('Open time:').$open_day['0'].'-'.$open_day['1'].'-'.$open_day['2'].' ';
                            $time_store .= __('Close time:').$close_day['0'].'-'.$close_day['1'].'-'.$close_day['2'].'<br>';
                        break;
                        case 3 : 
                            $time_store .= __('Tuesday : ');
                            $open_day = $store_time->tue_in_time;
                            $close_day = $store_time->tue_out_time;
                            $time_store .= __('Open time:').$open_day['0'].'-'.$open_day['1'].'-'.$open_day['2'].' ';
                            $time_store .= __('Close time:').$close_day['0'].'-'.$close_day['1'].'-'.$close_day['2'].'<br>';
                        break;
                        case 4 : 
                            $time_store .= __('Wednesday : ');
                            $open_day = $store_time->web_in_time;
                            $close_day = $store_time->web_out_time;
                            $time_store .= __('Open time:').$open_day['0'].'-'.$open_day['1'].'-'.$open_day['2'].' ';
                            $time_store .= __('Close time:').$close_day['0'].'-'.$close_day['1'].'-'.$close_day['2'].'<br>';
                        break;
                        case 5 : 
                            $time_store .= __('Thursday : ');
                            $open_day = $store_time->thu_in_time;
                            $close_day = $store_time->thu_out_time;
                            $time_store .= __('Open time:').$open_day['0'].'-'.$open_day['1'].'-'.$open_day['2'].' ';
                            $time_store .= __('Close time:').$close_day['0'].'-'.$close_day['1'].'-'.$close_day['2'].'<br>';
                        break;
                        case 6 : 
                            $time_store .= __('Friday : ');
                            $open_day = $store_time->fri_in_time;
                            $close_day = $store_time->fri_out_time;
                            $time_store .= __('Open time:').$open_day['0'].'-'.$open_day['1'].'-'.$open_day['2'].' ';
                            $time_store .= __('Close time:').$close_day['0'].'-'.$close_day['1'].'-'.$close_day['2'].'<br>';
                        break;
                        case 7 : 
                            $time_store .= __('Saturday');
                            $open_day = $store_time->sat_in_time;
                            $close_day = $store_time->sat_out_time;
                            $time_store .= __('Open time:').$open_day['0'].'-'.$open_day['1'].'-'.$open_day['2'].' ';
                            $time_store .= __('Close time:').$close_day['0'].'-'.$close_day['1'].'-'.$close_day['2'].'<br>';
                        break;
                        case 8 : 
                            $time_store .= __('Sunday : ');
                            $open_day = $store_time->sun_in_time;
                            $close_day = $store_time->sun_out_time;
                            $time_store .= __('Open time:').$open_day['0'].'-'.$open_day['1'].'-'.$open_day['2'].' ';
                            $time_store .= __('Close time:').$close_day['0'].'-'.$close_day['1'].'-'.$close_day['2'].'<br>';
                        break;
                    }
                }
                
                $detailShippingSeller[] = array(
                    'id'=>'pickup_'.$sp->getId(),
                    'name'=>'sellerstorepickup['.$useId.']',
                    'value'=>$this->scopeConfiguration->getValue('carriers/sellerstorepickup/price', ScopeInterface::SCOPE_STORE),
                    'price'=>$priceHelper->currency($this->scopeConfiguration->getValue('carriers/sellerstorepickup/price', ScopeInterface::SCOPE_STORE), true, false),
                    'title'=>$sp->getTitle(),
                    'detail_store'=>$detail_store,
                    'time_store'=>$time_store,
                    'class_detail_store'=>'class_detail_store_'.$useId,
                    'class_time_store'=>'class_time_store_'.$useId,
                    'store_id'=>$sp->getId()
                );
            }
            $dataShippingSeller['seller_store_pickup'][] = array(
                'seller_name'=>'Seller : '.$seller->getStoretitle().' - '.$priceHelper->currency($this->scopeConfiguration->getValue('carriers/sellerstorepickup/price', ScopeInterface::SCOPE_STORE), true, false), 
                'seller_id'=>$useId, 
                'input_name'=>'sellerstorepickup['.$useId.']', 
                'detail'=>$detailShippingSeller,
                'class_detail_store'=>'class_detail_store_'.$useId,
                'class_time_store'=>'class_time_store_'.$useId
            );
    	}
        
        return $dataShippingSeller;
    }
}