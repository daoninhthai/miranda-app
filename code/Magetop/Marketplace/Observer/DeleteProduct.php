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

class DeleteProduct implements ObserverInterface
{
    protected $_resource; 
	protected $_mkProduct;
	protected $_scopeConfig;
	protected $_saleslist;
    protected $_sellerpartner;   
	
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
		\Magetop\Marketplace\Model\ProductsFactory $mkProduct,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magetop\Marketplace\Model\SaleslistFactory  $saleslist,
        \Magetop\Marketplace\Model\PartnerFactory  $partner        
    )
    {
        $this->_resource = $resource;
        $this->_mkProduct = $mkProduct;
        $this->_scopeConfig = $scopeConfig;
        $this->_saleslist = $saleslist;
        $this->_sellerpartner = $partner;
    }

    //Action for delete product
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_mkProduct->create()
			 ->load($observer->getProduct()->getId(),'product_id')
			 ->delete();	
    }
}
