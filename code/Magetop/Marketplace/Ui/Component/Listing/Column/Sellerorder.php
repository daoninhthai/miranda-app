<?php
declare(strict_types=1);

namespace Magetop\Marketplace\Ui\Component\Listing\Column;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Directory\Model\Currency;
use Magetop\Marketplace\Model\SaleslistFactory;
use Magetop\Marketplace\Model\SellersFactory;

class Sellerorder extends Column
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceFormatter;

    /**
     * @var Currency
     */
    private $currency;

    /**
     * @var StoreManagerInterface|null
     */
    private $storeManager;

    protected $_salesList;
    protected $_sellers;
    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param PriceCurrencyInterface $priceFormatter
     * @param array $components
     * @param array $data
     * @param Currency $currency
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PriceCurrencyInterface $priceFormatter,
        SaleslistFactory $salesList,
        SellersFactory $sellers,
        array $components = [],
        array $data = [],
        Currency $currency = null,
        StoreManagerInterface $storeManager = null
    ) {
        $this->priceFormatter = $priceFormatter;
        $this->_salesList = $salesList;
        $this->_sellers = $sellers;
        $this->currency = $currency ?: ObjectManager::getInstance()
            ->get(Currency::class);
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()
            ->get(StoreManagerInterface::class);
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $orderId = $item['entity_id'] ?? null;
                $salesList = $this->_salesList->create();
                $filter = $salesList->load($orderId, 'orderid');
                if($filter->getData()){
                    $item[$this->getData('name')] = $this->_sellers->create()->load($filter->getData('sellerid'), 'user_id')->getData('storetitle');
                } else {
                    $item[$this->getData('name')] = 'Admin';
                }

            }
        }

        return $dataSource;
    }
}
