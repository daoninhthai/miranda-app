<?php
namespace Magetop\SellerDHLShipping\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        $installer->getConnection()->addColumn(
            $setup->getTable('multivendor_saleslist'),
            'shipment_label',
            [
                'type' => \Magento\Framework\DB\Ddl\Table::TYPE_VARBINARY,
                'length'=>'2m',
                'default' => null,
                'comment' => 'Shipping Label Content'
            ]
        );
        $installer->endSetup();
    }
}