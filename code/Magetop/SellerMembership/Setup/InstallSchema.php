<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Membership
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerMembership\Setup;

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
        
        // Check if the table multivendor_seller_membership already exists
        if ($installer->getConnection()->isTableExists($installer->getTable('multivendor_seller_membership')) != true) {
            // Create multivendor_seller_membership table
            $table = $installer->getConnection()->newTable(
                $installer->getTable('multivendor_seller_membership')
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
                    'product_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Product Id'
                )
                ->addColumn(
                    'title',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Title'
                )
                ->addColumn(
                    'fee',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Fee'
                )
                ->addColumn(
                    'time',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Time'
                )
                ->addColumn(
                    'number',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Number'
                )
                ->addColumn(
                    'commission',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Spencial % commission'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Status'
                )
                ->setComment('Multivendor Seller Membership')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        // Check if the table multivendor_seller_membership_detail already exists
        if ($installer->getConnection()->isTableExists($installer->getTable('multivendor_seller_membership_detail')) != true) {
            // Create multivendor_seller_membership_detail table
            $table = $installer->getConnection()->newTable(
                $installer->getTable('multivendor_seller_membership_detail')
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
                    'membership_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Membership Id'
                )
                ->addColumn(
                    'created_at',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'Created At'
                )
                ->addColumn(
                    'experi_date',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'Experi Date'
                )
                ->addColumn(
                    'total_number_product',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Total Number Product'
                )
                ->addColumn(
                    'remaining_number_product',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Remaining Number Product'
                )
                ->addColumn(
                    'paid_total',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Paid Total'
                )
                ->addColumn(
                    'paid_status',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Paid Status'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Status'
                )
                ->setComment('Multivendor Seller Membership')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        $setup->endSetup();
    }
}