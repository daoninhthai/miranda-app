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

            $sellerProduct = $this->sellerProductFactory->create()
                ->getCollection()
                ->addFieldToFilter('product_id', $productId)
                ->getFirstItem();

            if ($sellerProduct && $sellerProduct->getData('status') != 1) {
                $resultForward = $this->forwardFactory->create();
                return $resultForward->forward('noroute'); // Trả về 404
            }

        } catch (\Exception $e) {

        }

        return $proceed();
    }

    /**
     * Validates if a given value is a positive number.
     *
     * @param mixed $value
     * @return bool
     */
    protected function isPositiveNumber($value)
    {
        return is_numeric($value) && $value > 0;
    }

}
