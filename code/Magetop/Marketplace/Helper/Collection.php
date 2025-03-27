<?php
namespace Magetop\Marketplace\Helper;
use Magetop\Marketplace\Model\ResourceModel\Sellers\CollectionFactory as SellerCollection;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Magento\Store\Model\ScopeInterface;
/**
 * Magetop Marketplace Helper Data.
 */
class Collection extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerModel;

    /**
     * @var UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * @var \Magetop\Marketplace\Logger\Logger
     */
    protected $logger;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param SellerCollection $sellerCollectionFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerModel
     * @param UrlRewriteFactory $urlRewriteFactory
	 * @param \Magento\Framework\App\Cache\ManagerFactory $cacheManagerFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        SellerCollection $sellerCollectionFactory,
        \Magento\Customer\Model\CustomerFactory $customerModel,
        UrlRewriteFactory $urlRewriteFactory,
		\Magento\Framework\App\Cache\ManagerFactory $cacheManagerFactory,
		\Magento\Framework\Logger\Monolog $logger
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_sellerCollectionFactory = $sellerCollectionFactory;
        $this->customerModel = $customerModel;
        $this->urlRewriteFactory = $urlRewriteFactory;
		$this->cacheManager = $cacheManagerFactory;
		$this->logger = $logger;
    }
	public function getCollectionUrl($type=null)
    {
        if(!$type){
			$type='/collection/vendor';
		}
		$targetUrl = $this->getTargetUrlPath();
        if ($targetUrl) {
            $temp = explode($type, $targetUrl??'');
            if (!isset($temp[1])) {
                $temp[1] = '';
            }
            $temp = explode('/', $temp[1]??'');
            if (isset($temp[1]) && $temp[1] != '') {
                $temp1 = explode('?', $temp[1]??'');

                return $temp1[0];
            }
        }

        return false;
    }
	public function getTargetUrlPath()
    {
        try {
            $urls = explode(
                $this->_urlBuilder->getUrl(
                    '',
                    ['_secure' => $this->_request->isSecure()]
                ),
                $this->_urlBuilder->getCurrentUrl()??''
            );
            $targetUrl = '';
            if (empty($urls[1])) {
                $urls[1] = '';
            }
            $temp = explode('/?', $urls[1]??'');
            if (!isset($temp[1])) {
                $temp[1] = '';
            }
            if (!$temp[1]) {
                $temp = explode('?', $temp[0]??'');
            }
            $requestPath = $temp[0];
            $urlColl = $this->getUrlRewriteCollection()
                ->addFieldToFilter(
                    'request_path',
                    ['eq' => $requestPath]
                )
                ->addFieldToFilter(
                    'store_id',
                    ['eq' => $this->getCurrentStoreId()]
                );
            foreach ($urlColl as $value) {
                $targetUrl = $value->getTargetPath();
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data getTargetUrlPath : ".$e->getMessage());
            $targetUrl = '';
        }

        return $targetUrl;
    }
	public function logDataInLogger($data)
    {
        $this->logger->info($data);
    }
	public function getUrlRewriteCollection()
    {
        return $this->urlRewriteFactory->create()->getCollection();
    }
	public function getCurrentStoreId()
    {
        return $this->_storeManager->getStore()->getStoreId();
    }
	public function getSellerDataByShopUrl($shopUrl = '')
    {
        $collection = $this->getSellerCollectionObjByShop($shopUrl);
        $collection = $this->joinCustomer($collection);

        return $collection;
    }
	public function getSellerCollectionObjByShop($shopUrl)
    {
        $collection = $this->getSellerCollection();
        $collection->addFieldToFilter('is_vendor', 1);
        $collection->addFieldToFilter('storeurl', $shopUrl);
        $collection->addFieldToFilter('stores_id', $this->getCurrentStoreId());
        // If seller data doesn't exist for current store
        if (!$collection->getSize()) {
            $collection = $this->getSellerCollection();
            $collection->addFieldToFilter('is_vendor', 1);
            $collection->addFieldToFilter('storeurl', $shopUrl);
            $collection->addFieldToFilter('stores_id', 0);
        }

        return $collection;
    }
	public function joinCustomer($collection)
    {
        try {
            $collection->joinCustomer();
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Data joinCustomer : ".$e->getMessage());
            return $collection;
        }

        return $collection;
    }
	public function getSellerCollection()
    {
        return $this->_sellerCollectionFactory->create();
    }
	public function getProfileDetail($type = null)
    {
        $shopUrl = $this->getCollectionUrl($type);
        if (!$shopUrl) {
            $shopUrl = $this->_request->getParam('vendor');
        }

        if ($shopUrl) {
            $data = $this->getSellerCollectionObjByShop($shopUrl);
            foreach ($data as $seller) {
                return $seller;
            }
        }

        return false;
    }
	public function getRootCategoryIdByStoreId($storeId = 0)
    {
        return $this->_storeManager->getStore($storeId)->getRootCategoryId();
    }
	public function getRequestVar()
    {
        return "vendor";
    }
	public function isSellerFilterActive()
    {
        $filter = trim($this->_request->getParam($this->getRequestVar()));
        if ($filter != "") {
            return true;
        }

        return false;
    }
	/**
     * Check whether seller filter in layered navigation is allowed or not
     *
     * @return bool
     */
    public function allowSellerFilter()
    {
        return false;
    }
	/************
	* RewriteUrl *
	*************/
    public function getAllowUrlRewrite()
    {
        return $this->scopeConfig->getValue(
            'marketplace/general/url_rewrite',
            ScopeInterface::SCOPE_STORE
        );
    }
	public function clearCache()
    {
        $cacheManager = $this->cacheManager->create();
        $availableTypes = $cacheManager->getAvailableTypes();
        $cacheManager->clean($availableTypes);
    }
	public function getRewriteUrl($targetUrl)
    {
        $requestUrl = $this->_urlBuilder->getUrl(
            '',
            [
                '_direct' => $targetUrl,
                '_secure' => $this->_request->isSecure(),
            ]
        );
        $urlColl = $this->getUrlRewriteCollection()
            ->addFieldToFilter('target_path', $targetUrl)
            ->addFieldToFilter('store_id', $this->getCurrentStoreId());
        foreach ($urlColl as $value) {
            $requestUrl = $this->_urlBuilder->getUrl(
                '',
                [
                    '_direct' => $value->getRequestPath(),
                    '_secure' => $this->_request->isSecure(),
                ]
            );
        }

        return $requestUrl;
    }
	public function getRewriteUrlPath($targetUrl)
    {
        $requestPath = '';
        $urlColl = $this->getUrlRewriteCollection()
            ->addFieldToFilter(
                'target_path',
                $targetUrl
            )
            ->addFieldToFilter(
                'store_id',
                $this->getCurrentStoreId()
            );
        foreach ($urlColl as $value) {
            $requestPath = $value->getRequestPath();
        }

        return $requestPath;
    }
}
