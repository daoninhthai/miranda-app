<?php
/**
 * @author      Magetop Developer (Kien)
 * @package     Magento Multi Vendor Marketplace_Multiple_Flat_Rate_Shipping
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\SellerFlatRateShipping\CustomerData;
use Magento\Customer\CustomerData\SectionSourceInterface;
class Seller implements SectionSourceInterface
{
    /** @var \Magento\Catalog\Helper\Product\Compare */
    protected $helper;

    /** @var \Magento\Catalog\Model\Product\Url */
    protected $productUrl;
	protected $_catalogProductVisibility;
	protected $_productCollectionFactory;
    /**
     * @param \Magento\Catalog\Helper\Product\Compare $helper
     * @param \Magento\Catalog\Model\Product\Url $productUrl
     * @param \Magento\Catalog\Helper\Output $outputHelper
     */
    public function __construct(
        \Magento\Catalog\Helper\Product\Compare $helper,
        \Magento\Catalog\Model\Product\Url $productUrl,
		\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
		\Magento\Catalog\Model\Product\Visibility $catalogProductVisibility,
        \Magento\Catalog\Helper\Output $outputHelper
    ) {
        $this->helper = $helper;
        $this->productUrl = $productUrl;
        $this->outputHelper = $outputHelper;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_catalogProductVisibility = $catalogProductVisibility;		
    }

    /**
     * {@inheritdoc}
     */
    public function getSectionData()
    {
        $count = $this->helper->getItemCount();
        return [
            'count' => 1,
            'countCaption' => __('1 item') ,
            'listUrl' => 'dantri.com',
            'items' => $this->getItems(),
        ];
    }
	
	public function getItems(){
        $collection = $this->_productCollectionFactory->create();
        $collection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());	
		$collection->addMinimalPrice()
                   ->addFinalPrice()
                   ->addTaxPercents()
                   ->addAttributeToSelect('*')
                   ->addUrlRewrite();
		$collection->setPageSize(10)
                   ->setCurPage(1);
		$result1 = array();
		foreach($collection as $product){
			$result = array();
			$result['id'] = $product->getId();
			$result['name'] = $product->getName();	
			$result1[] = $result;			
		}
		return $result1;
	}
}