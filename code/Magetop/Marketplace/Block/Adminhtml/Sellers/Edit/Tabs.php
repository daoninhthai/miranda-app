<?php
/**
 * @author      Magetop Developer (David)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Block\Adminhtml\Sellers\Edit;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sellers_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Seller Information'));
    }
	
	protected function _beforeToHtml()
    {
        $moduleManager = \Magento\Framework\App\ObjectManager::getInstance()->create('Magento\Framework\Module\Manager');	
        if($moduleManager->isEnabled('Magetop_PaypalAdaptive')){
            $this->addTab('payment', array(
                'label' => __('Payment Info'),
                'title' => __('Payment Info'),
                'content' => $this->getLayout()->createBlock('Magetop\Marketplace\Block\Adminhtml\Sellers\Edit\Tab\Payment')->toHtml(),
            ));
        }

        $this->addTab(
            'related_products_section',
            [
                'label' => __('Seller Products'),
                'url' => $this->getUrl('marketplace/sellers/relatedProducts', ['_current' => true]),
                'class' => 'ajax',
            ]
        );
        
        if($moduleManager->isEnabled('Magetop_SellerStorePickup')){
            $this->addTab(
                'seller_store_pickup',
                [
                    'label' => __('Seller Store Pickup'),
                    'url' => $this->getUrl('magetop/sellerstorepickup/seller', ['_current' => true]),
                    'class' => 'ajax',
                ]
            );
        }
        return parent::_beforeToHtml();
    }
}