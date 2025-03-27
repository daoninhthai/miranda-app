<?php
/**
 * Magetop 
 * @category    Magetop 
 * @copyright   Copyright (c) 2017 Magetop (http://magetop.com/) 
 * @Author: Hanh Nguyen<hanhkaka.nguyen37@gamil.com>
 * @@Create Date: 2017-05-5
 * @@Modify Date: 2017-06-05
 */
namespace Magetop\Pslider\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

class UpgradeSchema implements UpgradeSchemaInterface {
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $tableName = $setup->getTable('pslider');
        if (version_compare($context->getVersion(), '2.0.0', '<')) {
            if ($setup->getConnection()->isTableExists($tableName) == true) {

            }
            else
            {
                $table = $setup->getConnection()->newTable(
                    $setup->getTable('pslider')
                )
                    ->addColumn(
                        'pslider_id',
                        Table::TYPE_INTEGER,11,
                        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                        'Slider ID'
                    )
                    ->addColumn(
                        'title',
                        Table::TYPE_TEXT,
                        255,
                        ['nullable' => false,'default' => ''],
                        'Slider Title'
                    )
                    ->addColumn(
                        'description',
                        Table::TYPE_TEXT,
                        null,
                        ['nullable' => false,'default' => ''],
                        'Slider Description'
                    )
                    ->addColumn(
                        'category_id',
                        Table::TYPE_SMALLINT,6,
                        ['nullable' => false],
                        'Category ID'
                    )
                    ->addColumn(
                        'type_id',
                        Table::TYPE_SMALLINT,6,
                        ['nullable' => false],
                        'Type ID'
                    )
                    ->addColumn(
                        'cats_id',
                        Table::TYPE_SMALLINT,6,
                        ['nullable' => false],
                        'Cat ID'
                    )
                    ->addColumn(
                        'position',
                        Table::TYPE_SMALLINT,6,
                        ['nullable' => false,'default' => 0],
                        'Slider Position'
                    )
                    ->addColumn(
                        'status',
                        Table::TYPE_SMALLINT,6,
                        ['nullable' => false,'default' => 0]
                    )
                    ->addColumn(
                        'created_time',
                        Table::TYPE_TIMESTAMP,null,
                        [],
                        'Creation Time'
                    )
                    ->addColumn(
                        'update_time',
                        Table::TYPE_TIMESTAMP,null,
                        [],
                        'Modification Time'
                    )
                    ->setComment(
                        'Product Slider Table'
                    );
                $setup->getConnection()->createTable($table);
            }
            if ($setup->getConnection()->isTableExists($tableName) == true) {

            }
            else
            {
                $table = $setup->getConnection()->newTable(
                    $setup->getTable('pslider_cats')
                )
                    ->addColumn(
                        'cats_id',
                        Table::TYPE_INTEGER,11,
                        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                        'Slider Cat ID'
                    )
                    ->addColumn(
                        'title',
                        Table::TYPE_TEXT,
                        255,
                        ['nullable' => false,'default' => ''],
                        'Cat Title'
                    )
                    ->addColumn(
                        'layout',
                        Table::TYPE_SMALLINT,6,
                        ['nullable' => false,'default' => 1],
                        'Layout ID'
                    )
                    ->addColumn(
                        'info',
                        Table::TYPE_TEXT,null,
                        ['nullable' => false,'default' => ''],
                        'Infomation'
                    )
                    ->addColumn(
                        'status',
                        Table::TYPE_SMALLINT,6,
                        ['nullable' => false,'default' => 0],
                        'Status'
                    )
                    ->addColumn(
                        'created_time',
                        Table::TYPE_TIMESTAMP,null,
                        [],
                        'Creation Time'
                    )
                    ->addColumn(
                        'update_time',
                        Table::TYPE_TIMESTAMP,null,
                        [],
                        'Modification Time'
                    )
                    ->setComment(
                        'Product Slider Categories Table'
                    );
                $setup->getConnection()->createTable($table);
            }
        }
        $setup->endSetup();
    }
}