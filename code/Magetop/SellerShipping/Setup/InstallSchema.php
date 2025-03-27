<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Shipping
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerShipping\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'seller_flat_rate_shipping',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'Seller Flat Rate Shipping',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'seller_flat_rate_shipping',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'Seller Flat Rate Shipping',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'seller_flat_rate_shipping',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'Seller Flat Rate Shipping',
            ]
        );
        
        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'seller_table_rate_shipping',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'Seller Table Rate Shipping',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'seller_table_rate_shipping',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'Seller Table Rate Shipping',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'seller_table_rate_shipping',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'Seller Table Rate Shipping',
            ]
        );
        
        $installer->getConnection()->addColumn(
            $installer->getTable('quote'),
            'seller_store_pickup_shipping',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'Seller Store Pickup Shipping',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'seller_store_pickup_shipping',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'Seller Store Pickup Shipping',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'seller_store_pickup_shipping',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'Seller Store Pickup Shipping',
            ]
        );

        $setup->endSetup();
    }
}