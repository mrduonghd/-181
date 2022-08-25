<?php
declare(strict_types=1);

namespace Webkul\MpApi\Model\Resolver\Seller;

use Webkul\MpApi\Model\ResourceModel\Saleslist\CollectionFactory as SaleslistCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Book field resolver, used for GraphQL request processing
 */
class OrderList implements ResolverInterface
{
    protected $sellerManagement;

    /**
     *
     * @param SellerManagement $sellerManagement
     */
    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionProcessorInterface $collectionProcessor,
        SaleslistCollectionFactory $saleslistCollectionFactory
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->collectionProcessor = $collectionProcessor;
        $this->saleslistCollectionFactory = $saleslistCollectionFactory;
    }
    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['filter'])) {
            throw new GraphQlInputException(
                __("'filter' input argument is required.")
            );
        }
        $fieldName = key($args['filter']);
        $filterType = key($args['filter'][$fieldName]);
        $fieldValue = $args['filter'][$fieldName][$filterType];
        $searchCriteria = $this->searchCriteriaBuilder->addFilter($fieldName, $fieldValue, $filterType)->create();

        $searchResult = $this->saleslistCollectionFactory
        ->create()
        ->addFieldToSelect(
            '*'
        )
        ->setOrder(
            'entity_id',
            'desc'
        );
        $this->collectionProcessor->process($searchCriteria, $searchResult);
        $searchResult->setSearchCriteria($searchCriteria);
        
        if ($searchResult->getSize() == 0) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('no sellers found')
            );
        }
        return $searchResult->toArray();
    }
}
