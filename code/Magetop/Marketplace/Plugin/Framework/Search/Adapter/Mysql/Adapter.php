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
namespace Magetop\Marketplace\Plugin\Framework\Search\Adapter\Mysql;

use Magento\Elasticsearch7\SearchAdapter\Mapper;
use Magento\Framework\DB\Select;

class Adapter
{
    /**
     * Mapper instance
     *
     * @var \Magento\Elasticsearch7\SearchAdapter\Mapper
     */
    protected $mysqlMapper;

    /**
     * Response Factory
     *
     * @var \Magento\Elasticsearch\SearchAdapter\ResponseFactory
     */
    protected $mysqlResponseFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\Elasticsearch\SearchAdapter\Aggregation\Builder
     */
    private $builder;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory
     */
    private $temporaryStorageFactory;

    /**
     * @var \Magetop\Marketplace\Helper\Collection
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magetop\Marketplace\Model\ResourceModel\Product\Collection
     */
    protected $_collection;

    /**
     * Query Select Parts to be skipped when prepare query for count
     *
     * @var array
     */
    private $countSqlSkipParts = [
        \Magento\Framework\DB\Select::LIMIT_COUNT => true,
        \Magento\Framework\DB\Select::LIMIT_OFFSET => true,
    ];

    /**
     * @param \Magento\Elasticsearch7\SearchAdapter\Mapper $mysqlMapper
     * @param \Magento\Elasticsearch\SearchAdapter\ResponseFactory $mysqlResponseFactory
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Elasticsearch\SearchAdapter\Aggregation\Builder $builder
     * @param \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory $temporaryStorageFactory
     * @param \Magetop\Marketplace\Helper\Collection $helper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magetop\Marketplace\Model\ResourceModel\Product\Collection $collection
     */
    public function __construct(
        \Magento\Elasticsearch7\SearchAdapter\Mapper $mysqlMapper,
        \Magento\Elasticsearch\SearchAdapter\ResponseFactory $mysqlResponseFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Elasticsearch\SearchAdapter\Aggregation\Builder $builder,
        \Magento\Framework\Search\Adapter\Mysql\TemporaryStorageFactory $temporaryStorageFactory,
        \Magetop\Marketplace\Helper\Collection $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Magetop\Marketplace\Model\ResourceModel\Products\Collection $collection
    ) {
        $this->mysqlMapper = $mysqlMapper;
        $this->mysqlResponseFactory = $mysqlResponseFactory;
        $this->resourceConnection = $resourceConnection;
        $this->builder = $builder;
        $this->temporaryStorageFactory = $temporaryStorageFactory;
        $this->helper = $helper;
        $this->request = $request;
        $this->_collection = $collection;
    }

    public function aroundQuery(
        \Magento\Framework\Search\Adapter\Mysql\Adapter $subject,
        callable $proceed,
        \Magento\Framework\Search\RequestInterface $request
    ) {
        if ($this->request->getFullActionName() == 'marketplace_seller_collection'|| $this->request->getFullActionName() == 'marketplace_seller_view') {
            $marketplaceProduct = $this->_collection->getTable('multivendor_product');
            $sellerId = $this->getProfileDetail()->getUserId();
            $updatedQuery = $this->mysqlMapper->buildQuery($request);
            $updatedQuery->join(
                ['mpp' => $marketplaceProduct],
                'mpp.product_id = main_select.entity_id',
                ''
            )->where("mpp.user_id = '".$sellerId."'");
            $temporaryStorage = $this->temporaryStorageFactory->create();
            $table = $temporaryStorage->storeDocumentsFromSelect($updatedQuery);

            $sellerDocuments = $this->getDocuments($table);
            $sellerAggregations = $this->builder->build(
                $request,
                $table,
                $sellerDocuments
            );
            $response = [
                'documents' => $sellerDocuments,
                'aggregations' => $sellerAggregations,
                'total' => $this->getSize($updatedQuery)
            ];
            return $this->mysqlResponseFactory->create($response);
        }
        return $proceed($request);
    }

    /**
     * Get Seller Profile Details
     *
     * @return \Magetop\Marketplace\Model\Seller | bool
     */
    public function getProfileDetail()
    {
        return $this->helper->getProfileDetail();
    }

    /**
     * Executes query and return raw response
     *
     * @param \Magento\Framework\DB\Ddl\Table $table
     * @return array
     * @throws Db_Exception
     */
    private function getDocuments(\Magento\Framework\DB\Ddl\Table $table)
    {
        $resourceConnection = $this->getConnection();
        $select = $resourceConnection->select();
        $select->from($table->getName(), ['entity_id', 'score']);
        return $resourceConnection->fetchAssoc($select);
    }

    /**
     * @return false|\Magento\Framework\DB\Adapter\AdapterInterface
     */
    private function getConnection()
    {
        return $this->resourceConnection->getConnection();
    }

    /**
     * Get rows size
     *
     * @param Select $query
     * @return int
     */
    private function getSize(Select $query): int
    {
        $sql = $this->getSelectCountSql($query);
        $parentSelect = $this->getConnection()->select();
        $parentSelect->from(['core_select' => $sql]);
        $parentSelect->reset(\Magento\Framework\DB\Select::COLUMNS);
        $parentSelect->columns('COUNT(*)');
        $totalRecords = $this->getConnection()->fetchOne($parentSelect);

        return intval($totalRecords);
    }

    /**
     * Reset limit and offset
     *
     * @param Select $query
     * @return Select
     */
    private function getSelectCountSql(Select $query): Select
    {
        foreach ($this->countSqlSkipParts as $part => $toSkip) {
            if ($toSkip) {
                $query->reset($part);
            }
        }

        return $query;
    }
}
