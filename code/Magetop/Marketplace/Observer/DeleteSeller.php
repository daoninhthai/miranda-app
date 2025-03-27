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

class DeleteSeller implements ObserverInterface
{
    protected $_resource; 
	protected $_mkProduct;
	protected $_scopeConfig;
	protected $_saleslist;
    protected $_sellers;   
    protected $_objectmanager;
    protected $_product;
	
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
		\Magetop\Marketplace\Model\ProductsFactory $mkProduct,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magetop\Marketplace\Model\SaleslistFactory  $saleslist,
        \Magetop\Marketplace\Model\SellersFactory  $sellers,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Catalog\Model\Product $product  
    )
    {
        $this->_resource = $resource;
        $this->_mkProduct = $mkProduct;
        $this->_scopeConfig = $scopeConfig;
        $this->_saleslist = $saleslist;
        $this->_sellers = $sellers;
        $this->_objectmanager = $objectmanager;  
        $this->_product = $product;        
    }

    //Action for delete seller
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
       	$sellerid = $observer->getCustomer()->getId();
        $this->_sellers->create()
			  ->load($sellerid,'user_id')
			  ->delete();
              
        $sellerpro = $this->_mkProduct->create()->getCollection()->addFieldToFilter('user_id',array('eq'=>$sellerid));
		foreach($sellerpro as $pro){
            $product_id = $pro['product_id'];
            $product = $this->_product->load($product_id);
            $product->delete();
			$pro->delete();
		}
    }
}
