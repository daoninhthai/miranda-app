<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Store_Pickup
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerStorePickup\Controller\Adminhtml\SellerStorePickup;
class Seller extends \Magetop\SellerStorePickup\Controller\Adminhtml\SellerStorePickup
{
	
    public function execute()
    {
		$model = $this->_getModel();				
        $this->_getRegistry()->register('current_model', $model);
        $this->_view->loadLayout()
             ->getLayout()
             ->getBlock('sellerstorepickup.sellerstorepickup.edit.tab.seller');
        $this->_view->renderLayout(); 
	}
}