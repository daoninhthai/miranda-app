<?php
namespace Magetop\SellerDHLShipping\Setup;

use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class InstallData implements InstallDataInterface
{
    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;

    /**
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory  $attributeSetFactory
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
    }

    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $setup]);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $attributeCodes = [
            ['value' => 'dhl_access_id', 'label' => __('Access Id')],
            ['value' => 'dhl_account_number', 'label' => __('Account Number')],
            ['value' => 'dhl_password', 'label' => __('Password')],
            ['value' => 'dhl_ready_time', 'label' => __('Ready Time')],
            ['value' => 'dhl_logo', 'label' => __('Display Logo')],
        ];
        foreach ($attributeCodes as $code) {
            $frontendClass = '';
            if ($code['value'] === 'account_id') {
                $frontendClass = 'validate-number';
            }
            $customerSetup->addAttribute(
                Customer::ENTITY, 
                $code['value'],
                [
                    'type' => 'varchar',
                    'label' => $code['label'],
                    'input' => 'text',
                    'frontend_class' => $frontendClass,
                    'required' => false,
                    'visible' => false,
                    'user_defined' => true,
                    'sort_order' => 1000,
                    'position' => 1000,
                    'system' => 0,
                ]
            );

            $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, $code['value'])->addData(
                [
                    'attribute_set_id' => $attributeSetId,
                    'attribute_group_id' => $attributeGroupId,
                    'used_in_forms' => [],
                ]
            );

            $attribute->save();
        }
    }
}