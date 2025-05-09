<?php
/**
 * Copyright © 2020 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magetop\Marketplace\Ui\DataProvider\Product\Form\Modifier;

use Magetop\Marketplace\Controller\Product\Initialization\StockDataFilter;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magetop\Marketplace\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;

/**
 * Data provider for advanced inventory form
 */
class AdvancedInventory extends AbstractModifier
{
    const STOCK_DATA_FIELDS = 'stock_data';

    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfiguration;

    /**
     * @var array
     */
    private $meta = [];

    /**
     * @param LocatorInterface $locator
     * @param StockRegistryInterface $stockRegistry
     * @param ArrayManager $arrayManager
     * @param StockConfigurationInterface $stockConfiguration
     */
    public function __construct(
        LocatorInterface $locator,
        StockRegistryInterface $stockRegistry,
        ArrayManager $arrayManager,
        StockConfigurationInterface $stockConfiguration
    ) {
        $this->locator = $locator;
        $this->stockRegistry = $stockRegistry;
        $this->arrayManager = $arrayManager;
        $this->stockConfiguration = $stockConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        $fieldCode = 'quantity_and_stock_status';

        $model = $this->locator->getProduct();
        $modelId = $model->getId();

        /** @var StockItemInterface $stockItem */
        $stockItem = $this->stockRegistry->getStockItem(
            $modelId,
            $model->getStore()->getWebsiteId()
        );

        $stockData = $modelId ? $this->getData($stockItem) : [];
        if (!empty($stockData)) {
            $data[$modelId][self::DATA_SOURCE_DEFAULT][self::STOCK_DATA_FIELDS] = $stockData;
        }
        if (isset($stockData['is_in_stock'])) {
            $data[$modelId][self::DATA_SOURCE_DEFAULT][$fieldCode]['is_in_stock'] =
                (int)$stockData['is_in_stock'];
        }

        if (!empty($this->stockConfiguration->getDefaultConfigValue(StockItemInterface::MIN_SALE_QTY))) {
            $minSaleQtyData = null;
            $defaultConfigValue = $this->stockConfiguration->getDefaultConfigValue(StockItemInterface::MIN_SALE_QTY);

            if (@strpos($defaultConfigValue, 'a:') === 0) {
                // Set data source for dynamicRows Minimum Qty Allowed in Shopping Cart
                $om = \Magento\Framework\App\ObjectManager::getInstance();
                $version = $om->get('Magetop\Marketplace\Helper\Data')->getMagentoVersion();
                if(version_compare($version, '2.2.0') >= 0){
                    $minSaleQtyValue = $om->get('Magento\Framework\Serialize\Serializer\Json')->unserialize($defaultConfigValue);
                }else{
                    $minSaleQtyValue = @unserialize($defaultConfigValue);
                }

                foreach ($minSaleQtyValue as $group => $qty) {
                    $minSaleQtyData[] = [
                        StockItemInterface::CUSTOMER_GROUP_ID => $group,
                        StockItemInterface::MIN_SALE_QTY => $qty
                    ];
                }
            } else {
                $minSaleQtyData = $defaultConfigValue;
            }

            $path = $modelId . '/' . self::DATA_SOURCE_DEFAULT . '/stock_data/min_qty_allowed_in_shopping_cart';
            $data = $this->arrayManager->set($path, $data, $minSaleQtyData);
        }

        return $data;
    }

    /**
     * Get Stock Data
     *
     * @param StockItemInterface $stockItem
     * @return array
     */
    private function getData(StockItemInterface $stockItem)
    {
        $result = $stockItem->getData();

        $result[StockItemInterface::MANAGE_STOCK] = (int)$stockItem->getManageStock();
        $result[StockItemInterface::QTY] = (float)$stockItem->getQty();
        $result[StockItemInterface::MIN_QTY] = (float)$stockItem->getMinQty();
        $result[StockItemInterface::MIN_SALE_QTY] = (float)$stockItem->getMinSaleQty();
        $result[StockItemInterface::MAX_SALE_QTY] = (float)$stockItem->getMaxSaleQty();
        $result[StockItemInterface::IS_QTY_DECIMAL] = (int)$stockItem->getIsQtyDecimal();
        $result[StockItemInterface::IS_DECIMAL_DIVIDED]= (int)$stockItem->getIsDecimalDivided();
        $result[StockItemInterface::BACKORDERS] = (int)$stockItem->getBackorders();
        $result[StockItemInterface::NOTIFY_STOCK_QTY] = (float)$stockItem->getNotifyStockQty();
        $result[StockItemInterface::ENABLE_QTY_INCREMENTS] = (int)$stockItem->getEnableQtyIncrements();
        $result[StockItemInterface::QTY_INCREMENTS] = (float)$stockItem->getQtyIncrements();
        $result[StockItemInterface::IS_IN_STOCK] = (int)$stockItem->getIsInStock();

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $this->meta = $meta;

        $this->prepareMeta();
		
        return $this->meta;
    }

    /**
     * @return void
     */
    private function prepareMeta()
    {
        $fieldCode = 'quantity_and_stock_status';
        $pathField = $this->arrayManager->findPath($fieldCode, $this->meta, null, 'children');

        if ($pathField) {
            $labelField = $this->arrayManager->get(
                $this->arrayManager->slicePath($pathField, 0, -2) . '/arguments/data/config/label',
                $this->meta
            );
            $fieldsetPath = $this->arrayManager->slicePath($pathField, 0, -4);

            $this->meta = $this->arrayManager->merge(
                $pathField . '/arguments/data/config',
                $this->meta,
                [
                    'label' => __('Stock Status'),
                    'value' => '1',
                    'dataScope' => $fieldCode . '.is_in_stock',
                    //'scopeLabel' => '[GLOBAL]',
                ]
            );
            $this->meta = $this->arrayManager->merge(
                $this->arrayManager->slicePath($pathField, 0, -2) . '/arguments/data/config',
                $this->meta,
                [
                    'label' => __('Stock Status'),
                    //'scopeLabel' => '[GLOBAL]',
                ]
            );

            $container['arguments']['data']['config'] = [
                'formElement' => 'container',
                'componentType' => 'container',
                'component' => "Magento_Ui/js/form/components/group",
				'template' => 'Magetop_Marketplace/group/group',
				'fieldTemplate' => 'Magetop_Marketplace/form/field',				
                'label' => $labelField,
                'breakLine' => false,
                'dataScope' => $fieldCode,
                //'scopeLabel' => '[GLOBAL]',
                'source' => 'product_details',
                'sortOrder' =>
                    (int) $this->arrayManager->get(
                        $this->arrayManager->slicePath($pathField, 0, -2) . '/arguments/data/config/sortOrder',
                        $this->meta
                    ) - 1,
            ];
            $qty['arguments']['data']['config'] = [
                'component' => 'Magetop_Marketplace/js/components/qty-validator-changer',
				//'template' => 'Magetop_Marketplace/form/field',
                'dataType' => 'number',
                'formElement' => 'input',
                'componentType' => 'field',
                'visible' => '1',
                'require' => '0',
                'additionalClasses' => 'admin__field-small',
                'label' => __('Quantity'),
                //'scopeLabel' => '[GLOBAL]',
                'dataScope' => 'qty',
                'validation' => [
                    'validate-number' => true,
                    'validate-digits' => true,
                    'less-than-equals-to' => StockDataFilter::MAX_QTY_VALUE,
                ],
                'imports' => [
                    'handleChanges' => '${$.provider}:data.product.stock_data.is_qty_decimal',
                ],
                'sortOrder' => 10,
            ];
            $advancedInventoryButton['arguments']['data']['config'] = [
                'displayAsLink' => true,
                'formElement' => 'container',
                'componentType' => 'container',
                'component' => 'Magento_Ui/js/form/components/button',
                'template' => 'Magetop_Marketplace/components/button/container',
                'actions' => [
                    [
                        'targetName' => 'magetopmarketproduct_form.magetopmarketproduct_form.advanced_inventory_modal',
                        'actionName' => 'toggleModal',
                    ],
                ],
                'title' => __('Advanced Inventory'),
				'modalClass' => 'seller-dashboard',
                'provider' => false,
                'additionalForGroup' => true,
                'source' => 'product_details',
                'sortOrder' => 40,
            ];
            $container['children'] = [
                'qty' => $qty,
                'advanced_inventory_button' => $advancedInventoryButton,
            ];

            $this->meta = $this->arrayManager->merge(
                $fieldsetPath . '/children',
                $this->meta,
                ['quantity_and_stock_status_qty' => $container]
            );
        }
    }
}
