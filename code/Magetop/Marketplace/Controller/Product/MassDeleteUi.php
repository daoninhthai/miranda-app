<?php
/**
 * @author      Magetop Developer (Hau)
 * @package     Magento Multi Vendor Marketplace
 * @copyright   Copyright (c) Magetop (https://www.magetop.com)
 * @terms       https://www.magetop.com/terms
 * @license     https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 **/
namespace Magetop\Marketplace\Controller\Product;
use Magento\Framework\App\Action\Action;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Model\Url as CustomerUrl;
use Magetop\Marketplace\Model\ResourceModel\Products\CollectionFactory as SellerProduct;
use Magetop\Marketplace\Helper\Data as HelperData;

/**
 * Magetop Marketplace Product MassDelete controller.
 */
class MassDeleteUi extends Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var SellerProduct
     */
    protected $_sellerProductCollectionFactory;

    /**
     * @var HelperData
     */
    protected $helper;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CustomerUrl
     */
    private $customerUrl;

    /**
     * @param Context           $context
     * @param Filter            $filter
     * @param Session           $customerSession
     * @param Registry          $coreRegistry
     * @param CollectionFactory $productCollectionFactory
     * @param SellerProduct     $sellerProductCollectionFactory
     * @param HelperData        $helper
     * @param ProductRepositoryInterface $productRepository
     * @param CustomerUrl       $customerUrl
     */
    public function __construct(
        Context $context,
        Filter $filter,
        Session $customerSession,
        Registry $coreRegistry,
        CollectionFactory $productCollectionFactory,
        SellerProduct $sellerProductCollectionFactory,
        HelperData $helper,
        ProductRepositoryInterface $productRepository = null,
        CustomerUrl $customerUrl
    ) {
        $this->filter = $filter;
        $this->_customerSession = $customerSession;
        $this->_coreRegistry = $coreRegistry;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_sellerProductCollectionFactory = $sellerProductCollectionFactory;
        $this->helper = $helper;
        $this->productRepository = $productRepository
            ?: \Magento\Framework\App\ObjectManager::getInstance()->create(ProductRepositoryInterface::class);
        parent::__construct(
            $context
        );
        $this->customerUrl = $customerUrl;
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->customerUrl->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Mass delete seller products action.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $isPartner = $this->helper->checkIsSeller();
        if ($isPartner == 1) {
            try {
                $registry = $this->_coreRegistry;
                if (!$registry->registry('mk_flat_catalog_flag')) {
                    $registry->register('mk_flat_catalog_flag', 1);
                }
				
                $collection = $this->filter->getCollection(
                    $this->_productCollectionFactory->create()
                );
                $ids = $collection->getAllIds();
                $wholedata = [];
                $sellerId = $this->helper->getCustomerId();
                $this->_coreRegistry->register('isSecureArea', 1);
                $deletedIdsArr = [];
                $sellerProducts = $this->_sellerProductCollectionFactory
                ->create()
                ->addFieldToFilter(
                    'product_id',
                    ['in' => $ids]
                )->addFieldToFilter(
                    'user_id',
                    $sellerId
                );
                foreach ($sellerProducts as $sellerProduct) {
                    array_push($deletedIdsArr, $sellerProduct['product_id']);
                    $wholedata['id'] = $sellerProduct['product_id'];
                    $this->_eventManager->dispatch(
                        'mk_delete_product',
                        [$wholedata]
                    );
                    $sellerProduct->delete();
                }

                foreach ($deletedIdsArr as $id) {
                    try {
                        $product = $this->productRepository->getById($id);
                        $this->productRepository->delete($product);
                    } catch (\Exception $e) {
                        $this->helper->logDataInLogger(
                            "Controller_Product_MassDeleteUi execute : ".$e->getMessage()
                        );
                        $this->messageManager->addError($e->getMessage());
                    }
                }

                $unauthIds = array_diff($ids, $deletedIdsArr);
                $this->_coreRegistry->unregister('isSecureArea');
                if (!count($unauthIds)) {
                    // clear cache
                    $this->helper->clearCache();
                    $this->messageManager->addSuccess(
                        __('A total of %1 record(s) have been deleted.', count($deletedIdsArr))
                    );
                }
            } catch (\Exception $e) {
                $this->helper->logDataInLogger(
                    "Controller_Product_MassDeleteUi execute : ".$e->getMessage()
                );
                $this->messageManager->addError($e->getMessage());
            }
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/seller/myProducts',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/seller/become',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
