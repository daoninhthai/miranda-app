<?php
/**
 * Magetop Development
 *
 * @category  Magetop Extension
 * @package   Magetop_Marketplace
 * @author    Magetop
 * @copyright Copyright (c) Magetop (https://www.magetop.com)
 * @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
namespace Magetop\Marketplace\Plugin\CatalogSearch\Model\Adapter\Aggregation\Checker\Query;

class CatalogView
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $requestInterface;

    /**
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $requestInterface
    ) {
        $this->requestInterface = $requestInterface;
    }

    /**
     * {@inheritdoc}
     */
    public function aroundIsApplicable(
        \Magento\CatalogSearch\Model\Adapter\Aggregation\Checker\Query\CatalogView $subject,
        callable $proceed,
        \Magento\Framework\Search\RequestInterface $request
    ) {
        $action = $this->requestInterface->getFullActionName();
        if ($action == 'marketplace_seller_collection'||$action == 'marketplace_seller_view') {
            $result = true;
            return $result;
        }
        return $proceed($request);
    }
}
