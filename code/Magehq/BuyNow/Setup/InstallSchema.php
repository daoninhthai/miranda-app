<?php
/**
 * Magehq
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Magehq.com license that is
 * available through the world-wide-web at this URL:
 * https://magehq.com/license.html
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Magehq
 * @package    Magehq_BuyNow
 * @copyright  Magehq\Copyright (c) 2022 Magehq (https://magehq.com/)
 * @license    https://magehq.com/license.html
 */
 
namespace Magehq\BuyNow\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\SchemaSetupInterface as SchemaSetup;
use Magento\Framework\Setup\ModuleContextInterface as ModuleContext;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class InstallSchema implements InstallSchemaInterface
{
    public function install(SchemaSetup $setup, ModuleContext $context)
    {
        $installer = $setup;
        $installer->startSetup();
        /*
         * Create table 'magehq_buy_now_quote_item'
         */
        $table = $setup->getConnection()->newTable(
                $setup->getTable('magehq_buy_now_quote_item')
        )->addColumn(
            'entity_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false, 'primary' => true, 'identity' => true],
            'entity_id'
        )->addColumn(
            'quote_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'quote_id'
        )->addColumn(
            'product_id',
            \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
            null,
            ['unsigned' => true, 'nullable' => false],
            'product_id'
        )->addColumn(
            'qty',
            \Magento\Framework\DB\Ddl\Table::TYPE_DECIMAL,
            255,
            ['nullable' => false],
            'Qty'
        )->addColumn(
            'info_buy_request',
            \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
            1000,
            ['nullable' => false],
            'Info Buy Request'
        )->setComment(
            'Temp Quote Item Table'
        );
        $installer->getConnection()->createTable($table);


        $installer->endSetup();
    }
}
