<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Multiple_Ups_Shipping
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerUpsShipping\Setup;

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
            'ups_shipping',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'Ups Shipping',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order'),
            'ups_shipping',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'Ups Shipping',
            ]
        );

        $installer->getConnection()->addColumn(
            $installer->getTable('sales_order_grid'),
            'ups_shipping',
            [
                'type' => 'text',
                'nullable' => false,
                'comment' => 'Ups Shipping',
            ]
        );
        // Check if the table multivendor_shipping_ups already exists
        if ($installer->getConnection()->isTableExists($installer->getTable('multivendor_shipping_ups')) != true) {
            // Create multivendor_shipping_ups table
            $table = $installer->getConnection()->newTable(
                $installer->getTable('multivendor_shipping_ups')
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
                    'access_license_number',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Access License Number'
                )
                ->addColumn(
                    'allowed_methods',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Allowed Methods'
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
                    'free_method',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Free Method'
                )
                ->addColumn(
                    'origin_shipment',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Origin Shipment'
                )
                ->addColumn(
                    'password',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Password'
                )
                ->addColumn(
                    'pickup',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Pickup'
                )
                ->addColumn(
                    'username',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Username'
                )
                ->addColumn(
                    'negotiated_active',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Negotiated Active'
                )
                ->addColumn(
                    'shipper_number',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Shipper Number'
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
                ->setComment('Multivendor Shipping Ups')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        $setup->endSetup();
    }
}