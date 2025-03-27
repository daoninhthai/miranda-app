<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Setup;
 
use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;
 
class UpgradeData implements UpgradeDataInterface
{
    public function upgrade(
        ModuleDataSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $setup->startSetup();
		
		if (version_compare($context->getVersion(), '1.0.3') < 0) {
			$magetopAtc = $setup->getTable('magetop_act');
			if ($setup->getConnection()->isTableExists($magetopAtc) != true) {
				$tableMagetopAtc = $setup->getConnection()->newTable(
                    $magetopAtc
                )->addColumn(
                        'act_id',
                        \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                        null,
                        ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                        'Id'
                    )
                    ->addColumn(
                        'domain_count',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        ['nullable' => false],
                        'Domain Count'
                    )
                    ->addColumn(
                        'domain_list',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        null,
                        ['nullable' => false],
                        'No comment'
                    )
                    ->addColumn(
                        'path',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        ['nullable' => false],
                        'No comment'
                    )
                    ->addColumn(
                        'extension_code',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        ['nullable' => false],
                        'No comment'
                    )
                    ->addColumn(
                        'act_key',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        255,
                        ['nullable' => false],
                        'No comment'
                    )
                    ->addColumn(
                        'domains',
                        \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                        500,
                        ['nullable' => false],
                        'No comment'
                    )
                    ->addColumn(
                        'created_time',
                        \Magento\Framework\DB\Ddl\Table::TYPE_DATE,
                        null,
                        ['nullable' => false],
                        'No comment'
                    )
                    ->addColumn(
                        'is_valid',
                        \Magento\Framework\DB\Ddl\Table::TYPE_SMALLINT,
                        null,
                        ['nullable' => false, 'default' => 0],
                        'No comment'
                    )
        			->setComment('Magetop ACT')
        			->setOption('type', 'InnoDB')
        			->setOption('charset', 'utf8');
				$setup->getConnection()->createTable($tableMagetopAtc);
			}
		}
        $setup->endSetup();
    }
}