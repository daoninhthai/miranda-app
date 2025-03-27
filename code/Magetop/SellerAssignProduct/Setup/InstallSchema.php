<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Assign_Product
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerAssignProduct\Setup;

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
        
        // Check if the table multivendor_assign_product already exists
        if ($installer->getConnection()->isTableExists($installer->getTable('multivendor_assign_product')) != true) {
            // Create multivendor_assign_product table
            $table = $installer->getConnection()->newTable(
                $installer->getTable('multivendor_assign_product')
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
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Product Id'
                )
                ->addColumn(
                    'price',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Price'
                )
                ->addColumn(
                    'qty',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Qty'
                )
                ->addColumn(
                    'product_condition',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Product Condition'
                )
                ->addColumn(
                    'product_description',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Product Description'
                )
                ->addColumn(
                    'image',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Image'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'Created At'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false, 'default' => '0'],
                    'Status'
                )
                ->setComment('Multivendor Assign Product')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }     
        
        // Check if the table multivendor_saleslist already exists
        if ($installer->getConnection()->isTableExists($installer->getTable('multivendor_saleslist')) == true) {
            $installer->getConnection()->addColumn(
                $installer->getTable('multivendor_saleslist'),
                'multivendor_assign_product_id',
                [
                    'type' => Table::TYPE_INTEGER,
                    'nullable' => false,
                    'comment' => 'Multivendor Assign Product Id',
                ]
            );
        }
        $setup->endSetup();
    }
}