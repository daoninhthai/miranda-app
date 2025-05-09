<?php
/**
 * Copyright � 2020 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magetop\Marketplace\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Api\ProductAttributeGroupRepositoryInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory as EavAttributeFactory;
use Magento\Eav\Api\Data\AttributeGroupInterface;
use Magento\Eav\Api\Data\AttributeInterface;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory as GroupCollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filter\Translit;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\Component\Form\Fieldset;
use Magento\Catalog\Ui\DataProvider\CatalogEavValidationRules;
use Magento\Ui\DataProvider\Mapper\FormElement as FormElementMapper;
use Magento\Ui\DataProvider\Mapper\MetaProperties as MetaPropertiesMapper;
use Magento\Ui\Component\Form\Element\Wysiwyg as WysiwygElement;
use Magento\Catalog\Model\Attribute\ScopeOverriddenValue;
use Magento\Framework\Locale\CurrencyInterface;

/**
 * Class Eav
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Eav extends AbstractModifier
{
    const SORT_ORDER_MULTIPLIER = 10;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var Config
     */
    protected $eavConfig;

    /**
     * @var CatalogEavValidationRules
     */
    protected $catalogEavValidationRules;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var GroupCollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var FormElementMapper
     */
    protected $formElementMapper;

    /**
     * @var MetaPropertiesMapper
     */
    protected $metaPropertiesMapper;

    /**
     * @var ProductAttributeGroupRepositoryInterface
     */
    protected $attributeGroupRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var ProductAttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var SortOrderBuilder
     */
    protected $sortOrderBuilder;

    /**
     * @var EavAttributeFactory
     */
    protected $eavAttributeFactory;

    /**
     * @var Translit
     */
    protected $translitFilter;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var ScopeOverriddenValue
     */
    private $scopeOverriddenValue;

    /**
     * @var array
     */
    private $attributesToDisable;

    /**
     * @var array
     */
    protected $attributesToEliminate;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var EavAttribute[]
     */
    private $attributes = [];

    /**
     * @var AttributeGroupInterface[]
     */
    private $attributeGroups = [];

    /**
     * @var array
     */
    private $canDisplayUseDefault = [];

    /**
     * @var array
     */
    private $bannedInputTypes = ['media_image'];

    /**
     * @var array
     */
    private $prevSetAttributes;

    /**
     * @var CurrencyInterface
     */
    private $localeCurrency;

    /**
     * @param LocatorInterface $locator
     * @param CatalogEavValidationRules $catalogEavValidationRules
     * @param Config $eavConfig
     * @param RequestInterface $request
     * @param GroupCollectionFactory $groupCollectionFactory
     * @param StoreManagerInterface $storeManager
     * @param FormElementMapper $formElementMapper
     * @param MetaPropertiesMapper $metaPropertiesMapper
     * @param ProductAttributeGroupRepositoryInterface $attributeGroupRepository
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     * @param EavAttributeFactory $eavAttributeFactory
     * @param Translit $translitFilter
     * @param ArrayManager $arrayManager
     * @param ScopeOverriddenValue $scopeOverriddenValue
     * @param DataPersistorInterface $dataPersistor
     * @param array $attributesToDisable
     * @param array $attributesToEliminate
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        LocatorInterface $locator,
        CatalogEavValidationRules $catalogEavValidationRules,
        Config $eavConfig,
        RequestInterface $request,
        GroupCollectionFactory $groupCollectionFactory,
        StoreManagerInterface $storeManager,
        FormElementMapper $formElementMapper,
        MetaPropertiesMapper $metaPropertiesMapper,
        ProductAttributeGroupRepositoryInterface $attributeGroupRepository,
        ProductAttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder,
        EavAttributeFactory $eavAttributeFactory,
        Translit $translitFilter,
        ArrayManager $arrayManager,
        ScopeOverriddenValue $scopeOverriddenValue,
        DataPersistorInterface $dataPersistor,
        $attributesToDisable = [],
        $attributesToEliminate = []
    ) {
        $this->locator = $locator;
        $this->catalogEavValidationRules = $catalogEavValidationRules;
        $this->eavConfig = $eavConfig;
        $this->request = $request;
        $this->groupCollectionFactory = $groupCollectionFactory;
        $this->storeManager = $storeManager;
        $this->formElementMapper = $formElementMapper;
        $this->metaPropertiesMapper = $metaPropertiesMapper;
        $this->attributeGroupRepository = $attributeGroupRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->attributeRepository = $attributeRepository;
        $this->sortOrderBuilder = $sortOrderBuilder;
        $this->eavAttributeFactory = $eavAttributeFactory;
        $this->translitFilter = $translitFilter;
        $this->arrayManager = $arrayManager;
        $this->scopeOverriddenValue = $scopeOverriddenValue;
        $this->dataPersistor = $dataPersistor;
        $this->attributesToDisable = $attributesToDisable;
        $this->attributesToEliminate = $attributesToEliminate;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        $sortOrder = 0;

        foreach ($this->getGroups() as $groupCode => $group) {
			//if($groupCode=='container_price') continue;
            $attributes = !empty($this->getAttributes()[$groupCode]) ? $this->getAttributes()[$groupCode] : [];
			
            if ($attributes) {
                $meta[$groupCode]['children'] = $this->getAttributesMeta($attributes, $groupCode);
                $meta[$groupCode]['arguments']['data']['config']['componentType'] = Fieldset::NAME;
                $meta[$groupCode]['arguments']['data']['config']['label'] = __('%1', $group->getAttributeGroupName());
                $meta[$groupCode]['arguments']['data']['config']['collapsible'] = true;
                $meta[$groupCode]['arguments']['data']['config']['dataScope'] = self::DATA_SCOPE_PRODUCT;
                $meta[$groupCode]['arguments']['data']['config']['sortOrder'] =
                    $sortOrder * self::SORT_ORDER_MULTIPLIER;
            }

            $sortOrder++;
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $moduleManager = $objectManager->create('Magento\Framework\Module\Manager'); 
        if($moduleManager->isEnabled('Magetop_ProductAttributeManagement')){
            foreach($meta as $key=>$data){
                foreach($data['children'] as $key1 => $val){
                    $k = explode('container_',$key1??'');
                    $data1 = \Magento\Framework\App\ObjectManager::getInstance()->create('Magetop\ProductAttributeManagement\Model\ProductAttributeManagement')->getCollection();
                    foreach($data1->getData() as $dt){
                        if(in_array($k[1],$dt)){
                            if($dt['status'] == 0){
                                unset($meta[$key]['children']['container_'.$k[1]]);
                            }
                        }
                    }                        
                }
            }     
        }    
        return $meta;
    }

    /**
     * Get attributes meta
     *
     * @param ProductAttributeInterface[] $attributes
     * @param string $groupCode
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getAttributesMeta(array $attributes, $groupCode)
    {
        $meta = [];

        foreach ($attributes as $sortOrder => $attribute) {
            if (in_array($attribute->getFrontendInput(), $this->bannedInputTypes)) {
                continue;
            }

            if (in_array($attribute->getAttributeCode(), $this->attributesToEliminate)) {
                continue;
            }

            if (!($attributeContainer = $this->setupAttributeContainerMeta($attribute))) {
                continue;
            }

            $attributeContainer = $this->addContainerChildren($attributeContainer, $attribute, $groupCode, $sortOrder);

            $meta[static::CONTAINER_PREFIX . $attribute->getAttributeCode()] = $attributeContainer;
        }

        return $meta;
    }

    /**
     * Add container children
     *
     * @param array $attributeContainer
     * @param ProductAttributeInterface $attribute
     * @param string $groupCode
     * @param int $sortOrder
     * @return array
     * @api
     */
    public function addContainerChildren(
        array $attributeContainer,
        ProductAttributeInterface $attribute,
        $groupCode,
        $sortOrder
    ) {
        foreach ($this->getContainerChildren($attribute, $groupCode, $sortOrder) as $childCode => $child) {
            $attributeContainer['children'][$childCode] = $child;
        }

        $attributeContainer = $this->arrayManager->merge(
            ltrim(static::META_CONFIG_PATH, ArrayManager::DEFAULT_PATH_DELIMITER),
            $attributeContainer,
            [
                'sortOrder' => $sortOrder * self::SORT_ORDER_MULTIPLIER,
                // TODO: Eliminate this in scope of MAGETWO-51364
                'scopeLabel' => $this->getScopeLabel($attribute),
            ]
        );

        return $attributeContainer;
    }

    /**
     * Retrieve container child fields
     *
     * @param ProductAttributeInterface $attribute
     * @param string $groupCode
     * @param int $sortOrder
     * @return array
     * @api
     */
    public function getContainerChildren(ProductAttributeInterface $attribute, $groupCode, $sortOrder)
    {
        if (!($child = $this->setupAttributeMeta($attribute, $groupCode, $sortOrder))) {
            return [];
        }

        return [$attribute->getAttributeCode() => $child];
    }

    /**
     * {@inheritdoc}
     */
    public function modifyData(array $data)
    {
        if (!$this->locator->getProduct()->getId() && $this->dataPersistor->get('catalog_product')) {
            return $this->resolvePersistentData($data);
        }

        $productId = $this->locator->getProduct()->getId();

        /** @var string $groupCode */
        foreach (array_keys($this->getGroups()) as $groupCode) {
            /** @var ProductAttributeInterface[] $attributes */
            $attributes = !empty($this->getAttributes()[$groupCode]) ? $this->getAttributes()[$groupCode] : [];

            foreach ($attributes as $attribute) {
                if (null !== ($attributeValue = $this->setupAttributeData($attribute))) {
                    if ($attribute->getFrontendInput() === 'price' && is_scalar($attributeValue)) {
                        $attributeValue = $this->formatPrice($attributeValue);
                    }
                    $data[$productId][self::DATA_SOURCE_DEFAULT][$attribute->getAttributeCode()] = $attributeValue;
                }
            }
        }

        return $data;
    }

    /**
     * Resolve data persistence
     *
     * @param array $data
     * @return array
     */
    private function resolvePersistentData(array $data)
    {
        $persistentData = (array)$this->dataPersistor->get('catalog_product');
        $this->dataPersistor->clear('catalog_product');
        $productId = $this->locator->getProduct()->getId();

        if (empty($data[$productId][self::DATA_SOURCE_DEFAULT])) {
            $data[$productId][self::DATA_SOURCE_DEFAULT] = [];
        }

        $data[$productId] = array_replace_recursive(
            $data[$productId][self::DATA_SOURCE_DEFAULT],
            $persistentData
        );

        return $data;
    }

    /**
     * Get product type
     *
     * @return null|string
     */
    private function getProductType()
    {
        return (string)$this->request->getParam('type', $this->locator->getProduct()->getTypeId());
    }

    /**
     * Return prev set id
     *
     * @return int
     */
    private function getPreviousSetId()
    {
        return (int)$this->request->getParam('prev_set_id', 0);
    }

    /**
     * Retrieve groups
     *
     * @return AttributeGroupInterface[]
     */
    private function getGroups()
    {
        if (!$this->attributeGroups) {
            $searchCriteria = $this->prepareGroupSearchCriteria()->create();
            $attributeGroupSearchResult = $this->attributeGroupRepository->getList($searchCriteria);			
            foreach ($attributeGroupSearchResult->getItems() as $group) {				
				if($group->getAttributeGroupCode() == 'product-details' || $group->getAttributeGroupCode()=='advanced-pricing' || $group->getAttributeGroupCode()=='content' || $group->getAttributeGroupCode()=='autosettings' || $group->getAttributeGroupCode()=='search-engine-optimization' )
				//if($group->getAttributeGroupCode() == 'product-details')
                $this->attributeGroups[$this->calculateGroupCode($group)] = $group;
				//$__group[] = $group->getAttributeGroupCode();
            }
        }		
        return $this->attributeGroups;
    }

    /**
     * Initialize attribute group search criteria with filters.
     *
     * @return SearchCriteriaBuilder
     */
    private function prepareGroupSearchCriteria()
    {
        return $this->searchCriteriaBuilder->addFilter(
            AttributeGroupInterface::ATTRIBUTE_SET_ID,
            $this->getAttributeSetId()
        );
    }

    /**
     * Return current attribute set id
     *
     * @return int|null
     */
    private function getAttributeSetId()
    {
        return $this->locator->getProduct()->getAttributeSetId();
    }

    /**
     * Retrieve attributes
     *
     * @return ProductAttributeInterface[]
     */
    private function getAttributes()
    {
        if (!$this->attributes) {
            foreach ($this->getGroups() as $group) {
                $this->attributes[$this->calculateGroupCode($group)] = $this->loadAttributes($group);
            }
        }

        return $this->attributes;
    }

    /**
     * Loading product attributes from group
     *
     * @param AttributeGroupInterface $group
     * @return ProductAttributeInterface[]
     */
    private function loadAttributes(AttributeGroupInterface $group)
    {
        $attributes = [];
        $sortOrder = $this->sortOrderBuilder
            ->setField('sort_order')
            ->setAscendingDirection()
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(AttributeGroupInterface::GROUP_ID, $group->getAttributeGroupId())
            ->addFilter(ProductAttributeInterface::IS_VISIBLE, 1)
            ->addSortOrder($sortOrder)
            ->create();
        $groupAttributes = $this->attributeRepository->getList($searchCriteria)->getItems();
        $productType = $this->getProductType();
        foreach ($groupAttributes as $attribute) {
            $applyTo = $attribute->getApplyTo();
            $isRelated = !$applyTo || in_array($productType, $applyTo);
            if ($isRelated) {
                $attributes[] = $attribute;
            }
        }

        return $attributes;
    }

    /**
     * Get attribute codes of prev set
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getPreviousSetAttributes()
    {
        if ($this->prevSetAttributes === null) {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('attribute_set_id', $this->getPreviousSetId())
                ->create();
            $attributes = $this->attributeRepository->getList($searchCriteria)->getItems();
            $this->prevSetAttributes = [];
            foreach ($attributes as $attribute) {
                $this->prevSetAttributes[] = $attribute->getAttributeCode();
            }
        }

        return $this->prevSetAttributes;
    }

    /**
     * Initial meta setup
     *
     * @param ProductAttributeInterface $attribute
     * @param string $groupCode
     * @param int $sortOrder
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @api
     */
    public function setupAttributeMeta(ProductAttributeInterface $attribute, $groupCode, $sortOrder)
    { 		
        $configPath = ltrim(static::META_CONFIG_PATH, ArrayManager::DEFAULT_PATH_DELIMITER);
		$formElement = $this->getFormElementsMapValue($attribute->getFrontendInput());
		$arrMeta = [
            'dataType' => $attribute->getFrontendInput(),
            'formElement' => $formElement,
            'visible' => $attribute->getIsVisible(),
            'required' => $attribute->getIsRequired(),
            'notice' => $attribute->getNote(),
            'default' => $attribute->getDefaultValue(),
            'label' => $attribute->getDefaultFrontendLabel(),
            'code' => $attribute->getAttributeCode(),
            'source' => $groupCode,
            'scopeLabel' => $this->getScopeLabel($attribute),
            'globalScope' => $this->isScopeGlobal($attribute),
            'sortOrder' => $sortOrder * self::SORT_ORDER_MULTIPLIER,
        ];
		if($formElement == 'select' && $attribute->getFrontendInput() == 'select'){
			$arrMeta['component'] = 'Magento_Ui/js/form/element/select';
			$arrMeta['template'] = 'Magetop_Marketplace/form/field';
			$arrMeta['elementTmpl'] = 'Magetop_Marketplace/form/element/select';			
		}else if($formElement == 'input' && (($attribute->getFrontendInput()=='text' && $attribute->getAttributeCode() != 'tier_price') || $attribute->getFrontendInput()=='weight') && $attribute->getFrontendInput() !='sku'){
			//$arrMeta['component'] = 'Magento_Ui/js/form/element/abstract';
			$arrMeta['template'] = 'Magetop_Marketplace/form/field';
			//if($attribute->getFrontendInput()=='weight'){
				$arrMeta['elementTmpl'] = 'Magetop_Marketplace/form/element/input';			
			//}
		}else if($attribute->getFrontendInput()=='price'){			
			//$arrMeta['dataType'] = 'text';
			$arrMeta['component'] = 'Magento_Ui/js/form/element/abstract';
			$arrMeta['template'] = 'Magetop_Marketplace/form/field';
			$arrMeta['elementTmpl'] = 'Magetop_Marketplace/form/element/input';			
			//$arrMeta['elementTmpl'] = 'Magetop_Marketplace/form/field';
		}else if($formElement=='date'){
			$arrMeta['component'] = 'Magento_Ui/js/form/element/date';
			$arrMeta['template'] = 'Magetop_Marketplace/form/field';						
			//$arrMeta['elementTmpl'] = 'Magetop_Marketplace/form/element/date';
		}else if($formElement == 'checkbox'){
			$arrMeta['component'] = 'Magento_Ui/js/form/element/single-checkbox';
			$arrMeta['template'] = 'Magetop_Marketplace/form/field';			
			//$arrMeta['elementTmpl'] = 'Magento_Ui/form/element/field';						
		}else if($formElement == 'multiselect'){
			$arrMeta['component'] = 'Magento_Ui/js/form/element/multiselect';
			$arrMeta['template'] = 'Magetop_Marketplace/form/field';						
		}else if($formElement == 'textarea'){
			$arrMeta['component'] = 'Magento_Ui/js/form/element/textarea';
			$arrMeta['template'] = 'Magetop_Marketplace/form/field';				
		}
        $meta = $this->arrayManager->set($configPath, [], $arrMeta);
		

        // TODO: Refactor to $attribute->getOptions() when MAGETWO-48289 is done
        $attributeModel = $this->getAttributeModel($attribute);
        if ($attributeModel->usesSource()) {
            $meta = $this->arrayManager->merge($configPath, $meta, [
                'options' => $attributeModel->getSource()->getAllOptions(),
            ]);
        }

        if ($this->canDisplayUseDefault($attribute)) {
            $meta = $this->arrayManager->merge($configPath, $meta, [
                'service' => [
                    'template' => 'ui/form/element/helper/service',
                ]
            ]);
        }

        if (!$this->arrayManager->exists($configPath . '/componentType', $meta)) {
            $meta = $this->arrayManager->merge($configPath, $meta, [
                'componentType' => Field::NAME,
            ]);
        }

        if (in_array($attribute->getAttributeCode(), $this->attributesToDisable)) {
            $meta = $this->arrayManager->merge($configPath, $meta, [
                'disabled' => true,
            ]);
        }

        // TODO: getAttributeModel() should not be used when MAGETWO-48284 is complete
        $childData = $this->arrayManager->get($configPath, $meta, []);
        if (($rules = $this->catalogEavValidationRules->build($this->getAttributeModel($attribute), $childData))) {
            $meta = $this->arrayManager->merge($configPath, $meta, [
                'validation' => $rules,
            ]);
        }

        $meta = $this->addUseDefaultValueCheckbox($attribute, $meta);

        switch ($attribute->getFrontendInput()) {
            case 'boolean':
                $meta = $this->customizeCheckbox($attribute, $meta);
                break;
            case 'textarea':
                $meta = $this->customizeWysiwyg($attribute, $meta);
                break;
            case 'price':
                $meta = $this->customizePriceAttribute($attribute, $meta);
                break;
            case 'gallery':
                // Gallery attribute is being handled by "Images And Videos" section
                $meta = [];
                break;
        }		
        return $meta;
    }

    /**
     * @param ProductAttributeInterface $attribute
     * @param array $meta
     * @return array
     */
    private function addUseDefaultValueCheckbox(ProductAttributeInterface $attribute, array $meta)
    {
        $canDisplayService = $this->canDisplayUseDefault($attribute);
        if ($canDisplayService) {
            $meta['arguments']['data']['config']['service'] = [
                'template' => 'ui/form/element/helper/service',
            ];

            $meta['arguments']['data']['config']['disabled'] = !$this->scopeOverriddenValue->containsValue(
                \Magento\Catalog\Api\Data\ProductInterface::class,
                $this->locator->getProduct(),
                $attribute->getAttributeCode(),
                $this->locator->getStore()->getId()
            );
        }
        return $meta;
    }

    /**
     * Setup attribute container meta
     *
     * @param ProductAttributeInterface $attribute
     * @return array
     * @api
     */
    public function setupAttributeContainerMeta(ProductAttributeInterface $attribute)
    {
        $containerMeta = $this->arrayManager->set(
            'arguments/data/config',
            [],
            [
                'formElement' => 'container',
                'componentType' => 'container',
                'breakLine' => false,
                'label' => $attribute->getDefaultFrontendLabel(),
                'required' => $attribute->getIsRequired(),
            ]
        );

        if ($attribute->getIsWysiwygEnabled()) {
            $containerMeta = $this->arrayManager->merge(
                'arguments/data/config',
                $containerMeta,
                [
                    'component' => 'Magento_Ui/js/form/components/group',
					'template' => 'Magetop_Marketplace/group/group',
					'fieldTemplate' => 'Magetop_Marketplace/form/field',													
                ]
            );
        }

        return $containerMeta;
    }

    /**
     * Setup attribute data
     *
     * @param ProductAttributeInterface $attribute
     * @return mixed|null
     * @api
     */
    public function setupAttributeData(ProductAttributeInterface $attribute)
    {
        $product = $this->locator->getProduct();
        $productId = $product->getId();
        $prevSetId = $this->getPreviousSetId();
        $notUsed = !$prevSetId
            || ($prevSetId && !in_array($attribute->getAttributeCode(), $this->getPreviousSetAttributes()));

        if ($productId && $notUsed) {
            return $this->getValue($attribute);
        }

        return null;
    }

    /**
     * Customize checkboxes
     *
     * @param ProductAttributeInterface $attribute
     * @param array $meta
     * @return array
     */
    private function customizeCheckbox(ProductAttributeInterface $attribute, array $meta)
    {
        if ($attribute->getFrontendInput() === 'boolean') {
            $meta['arguments']['data']['config']['prefer'] = 'toggle';
            $meta['arguments']['data']['config']['valueMap'] = [
                'true' => '1',
                'false' => '0',
            ];
        }

        return $meta;
    }

    /**
     * Customize attribute that has price type
     *
     * @param ProductAttributeInterface $attribute
     * @param array $meta
     * @return array
     */
    private function customizePriceAttribute(ProductAttributeInterface $attribute, array $meta)
    {
        if ($attribute->getFrontendInput() === 'price') {			
            $meta['arguments']['data']['config']['addbefore'] = $this->locator->getStore()
                ->getBaseCurrency()
                ->getCurrencySymbol();
        }

        return $meta;
    }

    /**
     * Add wysiwyg properties
     *
     * @param ProductAttributeInterface $attribute
     * @param array $meta
     * @return array
     */
    private function customizeWysiwyg(ProductAttributeInterface $attribute, array $meta)
    {
        if (!$attribute->getIsWysiwygEnabled()) {
            return $meta;
        }

        //$meta['arguments']['data']['config']['formElement'] = WysiwygElement::NAME;
        $meta['arguments']['data']['config']['formElement'] = 'wysiwyg';
        $meta['arguments']['data']['config']['component'] = 'Magetop_Marketplace/js/form/element/wysiwyg';
        $meta['arguments']['data']['config']['wysiwyg'] = true;
		if($meta['arguments']['data']['config']['code'] == 'description'){
			$meta['arguments']['data']['config']['content'] = '<textarea class=" textarea admin__control-textarea" cols="15" rows="20" name="description" id="magetopmarketproduct_form_description"></textarea>'            
			."<script type='text/javascript'>
				require([
					'jquery',
					'mage/translate', 
					'mage/adminhtml/events', 
					'mage/adminhtml/wysiwyg/tiny_mce/setup'
				], function(jQuery){
					wysiwygmagetopmarketproduct_form_description = new wysiwygSetup('magetopmarketproduct_form_description', {
						'width':'99%',
						'height':'200px',
						'plugins':[{'name':'image'}],
						'tinymce4':{'toolbar':'formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap','plugins':'advlist autolink lists link charmap media noneditable table contextmenu paste code help table'}
					});
					wysiwygmagetopmarketproduct_form_description.setup('exact');
				});
			</script>";
		}else if($meta['arguments']['data']['config']['code'] == 'short_description'){
			$meta['arguments']['data']['config']['content'] = '<textarea class=" textarea admin__control-textarea" cols="15" rows="20" name="short_description" id="magetopmarketproduct_form_short_description"></textarea>'
					."<script type='text/javascript'>
						require([
							'jquery',
							'mage/translate', 
							'mage/adminhtml/events', 
							'mage/adminhtml/wysiwyg/tiny_mce/setup'
						], function(jQuery){
							wysiwygmagetopmarketproduct_form_short_description = new wysiwygSetup('magetopmarketproduct_form_short_description', {
								'width':'99%',
								'height':'200px',
								'plugins':[{'name':'image'}],
								'tinymce4':{'toolbar':'formatselect | bold italic underline | alignleft aligncenter alignright | bullist numlist | link table charmap','plugins':'advlist autolink lists link charmap media noneditable table contextmenu paste code help table'}
							});
							wysiwygmagetopmarketproduct_form_short_description.setup('exact');
						});
					</script>";			
		}
        $meta['arguments']['data']['config']['wysiwygConfigData'] = [
            'add_variables' => false,
            'add_widgets' => false
        ];
        return $meta;
    }

    /**
     * Retrieve form element
     *
     * @param string $value
     * @return mixed
     */
    private function getFormElementsMapValue($value)
    {
        $valueMap = $this->formElementMapper->getMappings();

        return isset($valueMap[$value]) ? $valueMap[$value] : $value;
    }

    /**
     * Retrieve attribute value
     *
     * @param ProductAttributeInterface $attribute
     * @return mixed
     */
    private function getValue(ProductAttributeInterface $attribute)
    {
        /** @var Product $product */
        $product = $this->locator->getProduct();

        return $product->getData($attribute->getAttributeCode());
    }

    /**
     * Retrieve scope label
     *
     * @param ProductAttributeInterface $attribute
     * @return \Magento\Framework\Phrase|string
     */
    private function getScopeLabel(ProductAttributeInterface $attribute)
    {
        if (
            $this->storeManager->isSingleStoreMode()
            || $attribute->getFrontendInput() === AttributeInterface::FRONTEND_INPUT
        ) {
            return '';
        }

        /*switch ($attribute->getScope()) {
            case ProductAttributeInterface::SCOPE_GLOBAL_TEXT:
                return __('[GLOBAL]');
            case ProductAttributeInterface::SCOPE_WEBSITE_TEXT:
                return __('[WEBSITE]');
            case ProductAttributeInterface::SCOPE_STORE_TEXT:
                return __('[STORE VIEW]');
        }*/

        return '';
    }

    /**
     * Whether attribute can have default value
     *
     * @param ProductAttributeInterface $attribute
     * @return bool
     */
    private function canDisplayUseDefault(ProductAttributeInterface $attribute)
    {
        $attributeCode = $attribute->getAttributeCode();
        /** @var Product $product */
        $product = $this->locator->getProduct();

        if (isset($this->canDisplayUseDefault[$attributeCode])) {
            return $this->canDisplayUseDefault[$attributeCode];
        }

        return $this->canDisplayUseDefault[$attributeCode] = (
            ($attribute->getScope() != ProductAttributeInterface::SCOPE_GLOBAL_TEXT)
            && $product
            && $product->getId()
            && $product->getStoreId()
        );
    }

    /**
     * Check if attribute scope is global.
     *
     * @param ProductAttributeInterface $attribute
     * @return bool
     */
    private function isScopeGlobal($attribute)
    {
        return $attribute->getScope() === ProductAttributeInterface::SCOPE_GLOBAL_TEXT;
    }

    /**
     * Load attribute model by attribute data object.
     *
     * TODO: This method should be eliminated when all missing service methods are implemented
     *
     * @param ProductAttributeInterface $attribute
     * @return EavAttribute
     */
    private function getAttributeModel($attribute)
    {
        return $this->eavAttributeFactory->create()->load($attribute->getAttributeId());
    }

    /**
     * Calculate group code based on group name.
     *
     * TODO: This logic is copy-pasted from \Magento\Eav\Model\Entity\Attribute\Group::beforeSave
     * TODO: and should be moved to a separate service, which will allow two-way conversion groupName <=> groupCode
     * TODO: Remove after MAGETWO-48290 is complete
     *
     * @param AttributeGroupInterface $group
     * @return string
     */
    private function calculateGroupCode(AttributeGroupInterface $group)
    {
        $attributeGroupCode = $group->getAttributeGroupCode();

        if ($attributeGroupCode === 'images') {
            $attributeGroupCode = 'image-management';
        }

        return $attributeGroupCode;
    }

    /**
     * The getter function to get the locale currency for real application code
     *
     * @return \Magento\Framework\Locale\CurrencyInterface
     *
     * @deprecated
     */
    private function getLocaleCurrency()
    {
        if ($this->localeCurrency === null) {
            $this->localeCurrency = \Magento\Framework\App\ObjectManager::getInstance()->get(CurrencyInterface::class);
        }
        return $this->localeCurrency;
    }

    /**
     * Format price according to the locale of the currency
     *
     * @param mixed $value
     * @return string
     */
    protected function formatPrice($value)
    {
        if (!is_numeric($value)) {
            return null;
        }

        $store = $this->storeManager->getStore();
        $currency = $this->getLocaleCurrency()->getCurrency($store->getBaseCurrencyCode());
        $value = $currency->toCurrency($value, ['display' => \Magento\Framework\Currency::NO_SYMBOL]);

        return $value;
    }
}