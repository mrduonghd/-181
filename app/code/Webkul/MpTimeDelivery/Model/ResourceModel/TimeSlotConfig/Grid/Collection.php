<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpTimeDelivery
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpTimeDelivery\Model\ResourceModel\TimeSlotConfig\Grid;

use Webkul\MpTimeDelivery\Model\ResourceModel\TimeSlotConfig\Collection as TimeSlotConfigCollection;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Search\AggregationInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;

/**
 * Class Collection
 * Collection for displaying grid of seller slots
 */
class Collection extends TimeSlotConfigCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    protected $_aggregations;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface    $entityFactoryInterface
     * @param \Psr\Log\LoggerInterface                                     $loggerInterface
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategyInterface
     * @param \Magento\Framework\Event\ManagerInterface                    $eventManagerInterface
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManagerInterface
     * @param mixed|null                                                   $mainTable
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb         $eventPrefix
     * @param mixed                                                        $eventObject
     * @param mixed                                                        $resourceModel
     * @param string                                                       $model
     * @param null                                                         $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null    $resource
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactoryInterface,
        \Psr\Log\LoggerInterface $loggerInterface,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategyInterface,
        \Magento\Framework\Event\ManagerInterface $eventManagerInterface,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = Document::class,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactoryInterface,
            $loggerInterface,
            $fetchStrategyInterface,
            $eventManagerInterface,
            $storeManagerInterface,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * @return AggregationInterface
     */
    public function getAggregations()
    {
        return $this->_aggregations;
    }

    /**
     * @param AggregationInterface $aggregations
     * @return $this
     */
    public function setAggregations($aggregations)
    {
        $this->_aggregations = $aggregations;
    }

    /**
     * Retrieve all ids for collection
     * Backward compatibility with EAV collection
     *
     * @param  int $limit
     * @param  int $offset
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol(
            $this->_getAllIdsSelect($limit, $offset),
            $this->_bindParams
        );
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * Set search criteria.
     *
     * @param   \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return  $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null
    ) {
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param   int $totalCount
     * @return  $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param   \Magento\Framework\Api\ExtensibleDataInterface[] $items
     * @return  $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $customerGridFlat = $this->getTable('customer_grid_flat');
        $this->getSelect()->join(
            $customerGridFlat.' as cgf',
            'main_table.seller_id = cgf.entity_id',
            ['name' => 'name']
        );
        $this->addFilterToMap('name', 'cgf.name');
        parent::_renderFiltersBefore();
    }
}
