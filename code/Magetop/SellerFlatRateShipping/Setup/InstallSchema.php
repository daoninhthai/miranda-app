<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Multiple_Flat_Rate_Shipping
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerFlatRateShipping\Setup;

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
            'flat_rate_shipping',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'Flat Rate Shipping',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'flat_rate_shipping',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'Flat Rate Shipping',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'flat_rate_shipping',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'Flat Rate Shipping',
            ]
        );
        // Check if the table multivendor_shipping_flat_rate already exists
        if ($installer->getConnection()->isTableExists($installer->getTable('multivendor_shipping_flat_rate')) != true) {
            // Create multivendor_shipping_flat_rate table
            $table = $installer->getConnection()->newTable(
                $installer->getTable('multivendor_shipping_flat_rate')
            )->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true
                    ],
                    'ID'
                )
                ->addColumn(
                    'seller_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Seller Id'
                )
                ->addColumn(
                    'title',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Title'
                )
                ->addColumn(
                    'type',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Type'
                )
                ->addColumn(
                    'price',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Price'
                )
                ->addColumn(
                    'free_shipping',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Free Shipping'
                )
                ->addColumn(
                    'sort_order',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Sort Order'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Status'
                )
                ->setComment('Multivendor Shipping Flat Rate')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        $setup->endSetup();
    }
}