<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Store_Locator
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerStoreLocator\Setup;

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
        
        // Check if the table multivendor_seller_store_locator already exists
        if ($installer->getConnection()->isTableExists($installer->getTable('multivendor_seller_store_locator')) != true) {
            // Create multivendor_seller_store_locator table
            $table = $installer->getConnection()->newTable(
                $installer->getTable('multivendor_seller_store_locator')
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
                    'address',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Address'
                )
                ->addColumn(
                    'city',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'City'
                )
                ->addColumn(
                    'zipcode',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Zip Code'
                )
                ->addColumn(
                    'country',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Country'
                )
                ->addColumn(
                    'state',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'state'
                )
                ->addColumn(
                    'state_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'State Id'
                )
                ->addColumn(
                    'description',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Description'
                )
                ->addColumn(
                    'position',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Position'
                )
                ->addColumn(
                    'phone_number',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Phone Number'
                )
                ->addColumn(
                    'fax_number',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Fax Number'
                )
                ->addColumn(
                    'email',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Email'
                )
                ->addColumn(
                    'mapicon',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Map Icon'
                )
                ->addColumn(
                    'zoom_level',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Zoom Level'
                )
                ->addColumn(
                    'latitude',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Latitude'
                )
                ->addColumn(
                    'longitude',
                    Table::TYPE_FLOAT,
                    null,
                    ['nullable' => false],
                    'Longitude'
                )
                ->addColumn(
                    'shop_location',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Shop Location'
                )
                ->addColumn(
                    'created_time',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'Created Time'
                )
                ->addColumn(
                    'updated_time',
                    Table::TYPE_DATETIME,
                    null,
                    ['nullable' => false, 'default' => '0000-00-00 00:00:00'],
                    'Updated Time'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Status'
                )
                ->addColumn(
                    'stores_id',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Stores Id'
                )
                ->addColumn(
                    'store_time',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Stores Time'
                )
                ->setComment('Multivendor Seller Store Locator')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        // Check if the table multivendor_seller_store_locator_image already exists
        if ($installer->getConnection()->isTableExists($installer->getTable('multivendor_seller_store_locator_image')) != true) {
            // Create multivendor_seller_store_locator_image table
            $table = $installer->getConnection()->newTable(
                $installer->getTable('multivendor_seller_store_locator_image')
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
                    'image_delete',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Image Delete'
                )
                ->addColumn(
                    'options',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Options'
                )
                ->addColumn(
                    'name',
                    Table::TYPE_TEXT,
                    null,
                    ['nullable' => false, 'default' => ''],
                    'Name'
                )
                ->addColumn(
                    'status',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Status'
                )
                ->addColumn(
                    'multivendor_seller_store_locator_id',
                    Table::TYPE_INTEGER,
                    null,
                    ['nullable' => false],
                    'Multivendor Seller Store Locator Id'
                )
                ->setComment('Multivendor Seller Store Locator Image')
                ->setOption('type', 'InnoDB')
                ->setOption('charset', 'utf8');
            $installer->getConnection()->createTable($table);
        }
        $setup->endSetup();
    }
}