<?php
namespace Magetop\Marketplace\Plugin\Catalog\Controller\Product;

use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;

class View
{
    protected $forwardFactory;
    protected $productRepository;
    protected $sellerProductFactory;

    public function __construct(
        ForwardFactory $forwardFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magetop\Marketplace\Model\ProductsFactory $sellerProductFactory
    ) {
        $this->forwardFactory = $forwardFactory;
        $this->productRepository = $productRepository;
       $this->sellerProductFactory = $sellerProductFactory;
    }

    public function aroundExecute(
        \Magento\Catalog\Controller\Product\View $subject,
        callable $proceed
    ) {
        $productId = (int) $subject->getRequest()->getParam('id');

        try {
            $product = $this->productRepository->getById($productId);

            // Kiểm tra xem sản phẩm có thuộc seller marketplace không
            $sellerProduct = $this->sellerProductFactory->create()
                ->getCollection()
                ->addFieldToFilter('product_id', $productId)
                ->getFirstItem();

            // Nếu là sản phẩm của seller và chưa được admin approve
            if ($sellerProduct && $sellerProduct->getData('status') != 1) {
                $resultForward = $this->forwardFactory->create();
                return $resultForward->forward('noroute'); // Trả về 404
            }

        } catch (\Exception $e) {
            // Xử lý nếu có lỗi
        }

        return $proceed();
    }
}
