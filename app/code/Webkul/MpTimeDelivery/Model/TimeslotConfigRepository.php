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
namespace Webkul\MpTimeDelivery\Model;

use Webkul\MpTimeDelivery\Api\TimeslotConfigRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Webkul\MpTimeDelivery\Model\ResourceModel\TimeSlotConfig as ResourceTimeSlotConfig;
use Webkul\MpTimeDelivery\Model\ResourceModel\TimeSlotConfig\CollectionFactory as TimeSlotConfigCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface;
use Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterfaceFactory;
use Webkul\MpTimeDelivery\Api\Data\TimeslotConfigSearchResultsInterfaceFactory;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Class Timeslot Configuration Repository
 */
class TimeslotConfigRepository implements TimeslotConfigRepositoryInterface
{
    /**
     * @var Webkul\MpTimeDelivery\Model\ResourceModel\TimeSlotConfig
     */
    protected $resource;

    /**
     * @var Webkul\MpTimeDelivery\Model\TimeSlotConfigFactory
     */
    protected $timeSlotConfigFactory;

    /**
     * @var Webkul\MpTimeDelivery\Model\ResourceModel\TimeSlotConfig\CollectionFactory
     */
    protected $timeSlotCollectionFactory;

    /**
     * @var Webkul\MpTimeDelivery\Api\Data\TimeslotConfigSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var Magento\Framework\Reflection\DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterfaceFactory
     */
    protected $dataTimeslotConfigFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ResourceTimeSlotConfig                             $resource
     * @param TimeSlotConfigFactory                              $timeSlotConfigFactory
     * @param TimeSlotConfigCollectionFactory                    $timeSlotCollectionFactory
     * @param TimeslotConfigSearchResultsInterfaceFactory        $searchResultsFactory
     * @param DataObjectHelper                                   $dataObjectHelper
     * @param DataObjectProcessor                                $dataObjectProcessor
     * @param StoreManagerInterface                              $storeManager
     */
    public function __construct(
        ResourceTimeSlotConfig $resource,
        TimeSlotConfigFactory $timeSlotConfigFactory,
        TimeslotConfigInterfaceFactory $dataTimeslotConfigFactory,
        TimeSlotConfigCollectionFactory $timeSlotCollectionFactory,
        TimeslotConfigSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->timeSlotConfigFactory = $timeSlotConfigFactory;
        $this->timeSlotCollectionFactory = $timeSlotCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataTimeslotConfigFactory = $dataTimeslotConfigFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    /**
     * Save TimeSlot Configuration data
     *
     * @param  \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface $timeSlotConfig
     * @return TimeSlotConfiguration
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(TimeslotConfigInterface $timeSlotConfig)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $timeSlotConfig->setStoreId($storeId);
        try {
            $this->resource->save($timeSlotConfig);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $timeSlotConfig;
    }

    /**
     * Load TimeSlot Configuration data by id
     *
     * @param  string $id
     * @return TimeSlotConfiguration
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id)
    {
        $timeSlotConfig = $this->timeSlotConfigFactory->create();
        $this->resource->load($timeSlotConfig, $id);
        if (!$timeSlotConfig->getEntityId()) {
            throw new NoSuchEntityException(__('Time Slot with id "%1" does not exist.', $id));
        }
        return $timeSlotConfig;
    }

    /**
     * Load TimeSlot Configuration data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param   \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return  \Webkul\MpTimeDelivery\Model\ResourceModel\TimeSlotConfig\CollectionFactory
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $collection = $this->timeSlotCollectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            foreach ($filterGroup->getFilters() as $filter) {
                $condition = $filter->getConditionType() ?: 'eq';
                $collection->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $timeSlotData = [];
        /**
         *  @var TimeSlotConfiguration $timeSlotData
         */
        foreach ($collection as $timeSlotDataModel) {
            $timeSlot = $this->dataTimeslotConfigFactory->create();
            $this->dataObjectHelper->populateWithArray(
                $timeSlot,
                $timeSlotData->getData(),
                TimeslotConfigInterface::class
            );
            $timeSlotData[] = $this->dataObjectProcessor->buildOutputDataArray(
                $timeSlot,
                TimeslotConfigInterface::class
            );
        }
        $searchResults->setItems($timeSlotData);
        return $searchResults;
    }

    /**
     * Delete TimeSlot Configuration
     *
     * @param  \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface $timeSlotConfig
     * @return bool
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(TimeslotConfigInterface $timeSlotConfig)
    {
        try {
            $this->resource->delete($timeSlotConfig);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete TimeSlot Configuration by ID.
     *
     * @param  int $id
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id)
    {
        return $this->delete($this->getById($id));
    }
}
