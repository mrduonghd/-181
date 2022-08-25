<?php

namespace Mpx\PaypalJs\Model;

use Mpx\PaypalJs\Api\Data\PaypalAuthorizationInfoInterface;
use Mpx\PaypalJs\Api\PaypalAuthorizationInfoRepositoryInterface;
use Mpx\PaypalJs\Model\PaypalAuthorizationFactory;
use Mpx\PaypalJs\Model\ResourceModel\PaypalAuthorization as ObjectResourceModel;
use Mpx\PaypalJs\Model\ResourceModel\PaypalAuthorization\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;


/**
 * class PaypalAuthorizationInfoRepository
 * Crud Api PayPal
 */
class PaypalAuthorizationInfoRepository implements PaypalAuthorizationInfoRepositoryInterface
{
    protected $objectFactory;

    protected $objectResourceModel;

    protected $collectionFactory;

    protected $searchResultsFactory;

    public function __construct(
        PaypalAuthorizationFactory $objectFactory,
        ObjectResourceModel $objectResourceModel,
        CollectionFactory $collectionFactory,
        SearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->objectFactory        = $objectFactory;
        $this->objectResourceModel  = $objectResourceModel;
        $this->collectionFactory    = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * @param PaypalAuthorizationInfoInterface $object
     * @return PaypalAuthorizationInfoInterface
     * @throws CouldNotSaveException
     */
    public function save(PaypalAuthorizationInfoInterface $object): PaypalAuthorizationInfoInterface
    {
        try {
            $this->objectResourceModel->save($object);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }
        return $object;
    }

    /**
     * @param $id
     * @return PaypalAuthorizationInfoInterface
     * @throws NoSuchEntityException
     */
    public function getById($id): PaypalAuthorizationInfoInterface
    {
        $object = $this->objectFactory->create();
        $this->objectResourceModel->load($object, $id);
        if (!$object->getId()) {
            throw new NoSuchEntityException(__('Paypal Authorization Info with id "%1" does not exist.', $id));
        }
        return $object;
    }

    /**
     * Retrieve Paypal Authorization Info list.
     *
     * @param SearchCriteriaInterface $criteria
     * @return PaypalAuthorizationInfoInterface|\Magento\Framework\Api\SearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $collection = $this->collectionFactory->create();
        foreach ($criteria->getFilterGroups() as $filterGroup) {
            $fields     = [];
            $conditions = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $condition    = $filter->getConditionType() ? $filter->getConditionType() : 'eq';
                $fields[]     = $filter->getField();
                $conditions[] = [$condition => $filter->getValue()];
            }
            if ($fields) {
                $collection->addFieldToFilter($fields, $conditions);
            }
        }
        $searchResults->setTotalCount($collection->getSize());
        $sortOrders = $criteria->getSortOrders();
        if ($sortOrders) {
            /** @var SortOrder $sortOrder */
            foreach ($sortOrders as $sortOrder) {
                $collection->addOrder(
                    $sortOrder->getField(),
                    ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                );
            }
        }
        $collection->setCurPage($criteria->getCurrentPage());
        $collection->setPageSize($criteria->getPageSize());
        $objects = [];
        foreach ($collection as $objectModel) {
            $objects[] = $objectModel;
        }
        $searchResults->setItems($objects);
        return $searchResults;
    }

    /**
     * Delete Paypal Authorization Info.
     *
     * @param PaypalAuthorizationInfoInterface $object
     * @return bool true on success
     * @throws CouldNotDeleteException
     */
    public function delete(PaypalAuthorizationInfoInterface $object): bool
    {
        try {
            $this->objectResourceModel->delete($object);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Delete Paypal Authorization Info by ID.
     *
     * @param $id
     * @return bool true on success
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($id): bool
    {
        return $this->delete($this->getById($id));
    }
}
