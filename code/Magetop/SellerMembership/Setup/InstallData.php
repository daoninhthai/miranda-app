<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Seller_Membership
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerMembership\Setup;

use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magetop\SellerMembership\Model\Product\Type\Membership as MembershipType;
/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
   /**
     * EAV setup factory
     *
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * Init
     *
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(EavSetupFactory $eavSetupFactory)
    {
        $this->eavSetupFactory = $eavSetupFactory;
    }
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
		$eavSetup = $this->eavSetupFactory->create(['setup' => $setup]);
		$attributes = [
				'cost',
				'price',
				'special_price',
				'tax_class_id'
			];
		foreach ($attributes as $attributeCode) {
			$relatedProductTypes = explode(
				',',
				$eavSetup->getAttribute(\Magento\Catalog\Model\Product::ENTITY, $attributeCode, 'apply_to')??''
			);
			if (!in_array(MembershipType::TYPE_CODE, $relatedProductTypes)) {
				$relatedProductTypes[] = MembershipType::TYPE_CODE;
				$eavSetup->updateAttribute(
					\Magento\Catalog\Model\Product::ENTITY,
					$attributeCode,
					'apply_to',
					implode(',', $relatedProductTypes)
				);
			}
		}
    }
}
