<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Multiple_Table_Rate_Shipping
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerTableRateShipping\Setup;

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
            'table_rate_shipping',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'Table Rate Shipping',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'table_rate_shipping',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'Table Rate Shipping',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'table_rate_shipping',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'Table Rate Shipping',
            ]
        );
        // Check if the table multivendor_shipping_table_rate already exists
        if ($installer->getConnection()->isTableExists($installer->getTable('multivendor_shipping_table_rate')) != true) {
            // Create multivendor_shipping_table_rate table
            $table = $installer->getConnection()->newTable(
                $installer->getTable('multivendor_shipping_table_rate')
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
                    'translate_title',
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
                    'country_code',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Country Code'
                )
                ->addColumn(
                    'region_id',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Region Id'
                )
                ->addColumn(
                    'zip_from',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Zip From'
                )
                ->addColumn(
                    'zip_to',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Zip To'
                )
                ->addColumn(
                    'weight_from',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Weight From'
                )
                ->addColumn(
                    'weight_to',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Weight To'
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
                ->setComment('Multivendor Shipping Table Rate')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        $setup->endSetup();
    }
}