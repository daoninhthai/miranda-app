<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Multiple_Usps_Shipping
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerUspsShipping\Setup;

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
            'usps_shipping',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'Usps Shipping',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'usps_shipping',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'Usps Shipping',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'usps_shipping',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'Usps Shipping',
            ]
        );
        // Check if the table multivendor_shipping_usps already exists
        if ($installer->getConnection()->isTableExists($installer->getTable('multivendor_shipping_usps')) != true) {
            // Create multivendor_shipping_usps table
            $table = $installer->getConnection()->newTable(
                $installer->getTable('multivendor_shipping_usps')
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
                    'enable',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Enable'
                )
                ->addColumn(
                    'userid',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Userid'
                )
                ->addColumn(
                    'password',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Password'
                )
                ->addColumn(
                    'allowed_methods',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Allowed Methods'
                )
                ->addColumn(
                    'free_method',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Free Method'
                )
                ->addColumn(
                    'free_shipping_enable',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Free Shipping Enable'
                )
                ->addColumn(
                    'free_shipping_subtotal',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Free Shipping Subtotal'
                )
                ->addColumn(
                    'sallowspecific',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Sallow Specific'
                )
                ->addColumn(
                    'specificcountry',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Specific Country'
                )
                ->setComment('Multivendor Shipping Usps')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        $setup->endSetup();
    }
}