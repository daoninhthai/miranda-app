<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Store_Pickup
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerStorePickup\Model;

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
        \Magetop\SellerStorePickup\Model\SellerStorePickupFactory $mkShipping
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
        $enabled = $this->scopeConfiguration->getValue('carriers/sellerstorepickup/active', ScopeInterface::SCOPE_STORE);
        $dataShippingSeller['store_pickup_enable'] = ($enabled)?true:false;
        
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
                    'id'=>'id_'.$sp->getId(),
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