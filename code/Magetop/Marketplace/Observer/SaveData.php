<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\ScopeInterface;

class SaveData implements ObserverInterface
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession; 
    protected $_resource; 
	protected $_mkProduct;
	protected $_scopeConfig;
	protected $_saleslist;
    protected $_sellerpartner;   
	
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,  
        \Magento\Framework\App\ResourceConnection $resource,
		\Magetop\Marketplace\Model\ProductsFactory $mkProduct,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magetop\Marketplace\Model\SaleslistFactory  $saleslist,
        \Magetop\Marketplace\Model\PartnerFactory  $partner        
    )
    {
        $this->_checkoutSession = $checkoutSession; 
        $this->_resource = $resource;
        $this->_mkProduct = $mkProduct;
        $this->_scopeConfig = $scopeConfig;
        $this->_saleslist = $saleslist;
        $this->_sellerpartner = $partner;
    }

    //Action for complete order
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
		$items = $order->getAllVisibleItems();
		$mkProductModel = $this->_mkProduct->create();
		
		$selesListModel = $this->_saleslist->create();
		if(count($items))
		{
            $moduleManager = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Framework\Module\Manager');
            $advanced_commissions = null;
            $sellerids = array();
			foreach($items as $item)
			{
                $percentCommision = $this->_scopeConfig->getValue('marketplace/general/percent',ScopeInterface::SCOPE_STORE); 
				$_product = $item->getProduct();
				$mkProductCollection = $mkProductModel->getCollection()
                                        ->addFieldToFilter('product_id',$item->getProductId())
                                        ->addFieldToFilter('status',1);
                                        
                //Kien 19/5/2020 - update filter seller approve        
                $tableMKuser = $this->_resource->getTableName('multivendor_user');
                $mkProductCollection->getSelect()->joinLeft(array('mk_user'=>$tableMKuser),'main_table.user_id = mk_user.user_id',array())
                    ->where('mk_user.userstatus = 1'); 
                            
                //Assign product                                                                                         
				$sellerId = 0;
                $productOption = $item->getProductOptions();
                $infoBuyRequest = $productOption['info_buyRequest'];
                if(@$infoBuyRequest['assignproduct_id']){
                    $SellerAssignProduct = \Magento\Framework\App\ObjectManager::getInstance()->create('\Magetop\SellerAssignProduct\Model\SellerAssignProduct')->load($infoBuyRequest['assignproduct_id']);
                    $sellerId = $SellerAssignProduct->getSellerId();
                    $multivendor_assign_product_id = $infoBuyRequest['assignproduct_id'];                    
                }else{
    				if(count($mkProductCollection))
    				{
    					foreach($mkProductCollection as $mkProductCollect)
    					{
    						$sellerId = $mkProductCollect->getUserId();
                            $multivendor_assign_product_id = 0;                            
    						break;
    					}
    				}
                }
                //end Assign product
                
				if($sellerId == 0)
				{
					continue;
				}
                //SellerCoupon
                $sellerids[$sellerId] = $sellerId;
                //end SellerCoupon
                	
        		if($moduleManager->isEnabled('Magetop_AdvancedCommission')){
                    $advanced_commissions = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\AdvancedCommission\Helper\Data')
                                                                                               ->getCommission($_product);
                }else{
                    $advanced_commissions = null;
                }
                $seller = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\Marketplace\Model\Sellers')->getCollection()->addFieldToFilter('user_id',$sellerId)->getFirstItem();
                $seller_commission = $seller['commission'];
                
                //Membership 
                if($moduleManager->isEnabled('Magetop_SellerMembership') && \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\Marketplace\Helper\Data')->getSellerMembershipIsEnabled()){
                    $membershipData = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\SellerMembership\Model\SellerMembership')
                                                                                                 ->getCollection()
                                                                                                 ->addFieldToFilter('seller_id',$sellerId)
                                                                                                 ->getFirstItem();
                    
                    if($membershipData['membership_id']){                                                                             
                        if(strtotime($membershipData['experi_date']) >= strtotime(date("Y-m-d"))){
                            $membership = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\SellerMembership\Model\Membership')->load($membershipData['membership_id']);
                            $percentCommision = $membership->getCommission();
                        }else{
                            if($advanced_commissions){
                                $percentCommision = $advanced_commissions;
                            }else{
                                if($seller_commission){
                                    $percentCommision = $seller_commission;
                                }else{
                                    $percentCommision = $percentCommision;
                                }
                            } 
                        }
                    }else{
                        if($advanced_commissions){
                            $percentCommision = $advanced_commissions;
                        }else{
                            if($seller_commission){
                                $percentCommision = $seller_commission;
                            }else{
                                $percentCommision = $percentCommision;
                            }
                        } 
                    }
                }else{
                //End membership  
                    if($advanced_commissions){
                        $percentCommision = $advanced_commissions;
                    }else{
                        if($seller_commission){
                            $percentCommision = $seller_commission;
                        }else{
                            $percentCommision = $percentCommision;
                        }
                    } 
                }
                $orderRepos = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Sales\Api\OrderRepositoryInterface')->get($order->getId());
                if(stripos($orderRepos->getShippingMethod(), 'sellerflatrate') !== false){        
                    $shippings = explode(",", $orderRepos->getSellerFlatRateShipping()); 
                    foreach ($shippings as $shipping) {
                        $parts = explode("-", $shipping); 
                        $id = $parts[0];
                        $value = $parts[1];

                        $value = str_replace("$", "", $value);
                        $value = floatval($value);
                        if ($sellerId == $id) {
                            $shippingPrice = $value; 
                            break; 
                        }
                    }
                } else {
                    $shippingPrice = $orderRepos->getShippingAmount() * (count($mkProductCollection)/count($items));
                }
                $totalamount = $item->getBaseRowTotal();
                $proqty = $totalamount/$item->getBasePrice();
				$totalcommision = ($item->getBaseRowTotal() * $percentCommision) / 100;
				$actualparterprocost = $item->getBaseRowTotal() - $totalcommision;
				$dataSave = array(
                    'prodid'=>$item->getProductId(), 
					'orderid'=>$order->getId(),
					'realorderid'=>$order->getIncrementId(),
					'sellerid'=>$sellerId,
					'buyerid'=>$order->getCustomerId(),
                    'order_status'=>$order->getStatus(),
					'proprice'=>$item->getBasePrice(),
					'proname'=>$item->getName(),
                    'proqty'=>$proqty,
					'totalamount'=>$totalamount + $shippingPrice,
					'totalcommision'=>$totalcommision,
                    'actualparterprocost'=>$actualparterprocost + $shippingPrice,
					'paidstatus'=>0,
					'transid'=>0,
					'totaltax'=>0,
                    'multivendor_assign_product_id'=>$multivendor_assign_product_id                    
				);
				$selesListModel->setData($dataSave)->save();
			}
            //SellerCoupon
            if($moduleManager->isEnabled('Magetop_SellerCoupon')){
                if($this->_checkoutSession->getData('seller_coupon_price')){
                    $seller_coupon = $this->_checkoutSession->getData('seller_coupon_price');
                    foreach($sellerids as $id){
                        if(@$seller_coupon[$id][1]){
                            $sellerCoupon = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\SellerCoupon\Model\SellerCoupon')->getCollection()->addFieldToFilter('seller_coupon_code',$seller_coupon[$id][0]);
                            foreach($sellerCoupon as $row){
                                $row->setOrderId($order->getId());
                                $row->setUsedDescription('Used for order #'.$order->getIncrementId());
                                $time = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Framework\Stdlib\DateTime\Timezone');
                                $row->setUsedDate(date('Y-m-d H:i:s',$time->scopeTimeStamp()));
                                $row->setUsedStatus(1);
                                $row->setOrderStatus($order->getStatus());
                                $row->setFinalPriceUsed($seller_coupon[$id][1]);
                                $row->save();
                            }
                        }
                    }
                    $this->_checkoutSession->setData('seller_coupon_price',null);
                }
            }
            //end SellerCoupon
		}
    }
}
