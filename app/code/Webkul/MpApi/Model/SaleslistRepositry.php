<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpApi
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 namespace Webkul\MpApi\Model;

 use Webkul\MpApi\Model\ResourceModel\Saleslist\CollectionFactory as SaleslistCollectionFactory;
 use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
 use Magento\Framework\App\ObjectManager;

class SaleslistRepositry implements \Webkul\MpApi\Api\SaleslistInterface
{
    /**
     * @var Data\SellerResultsInterface
     */
    protected $sellerResultsFactory;

    /**
     * @var SaleslistCollectionFactory
     */
    protected $saleslistCollectionFactory;

    /**
     * @param Data\SellerResultsInterface $sellerResultsFactory
     * @param SaleslistCollectionFactory $saleslistCollectionFactory
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor = null,
        SaleslistCollectionFactory $saleslistCollectionFactory
    ) {
        $this->collectionProcessor = $collectionProcessor ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class);
        $this->saleslistCollectionFactory = $saleslistCollectionFactory;
    }

    /**
     * @inheritDoc
     */
    public function getList($id, \Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        try {
            $sellerId = $id;
            $searchResult = $this->saleslistCollectionFactory->create()->addFieldToFilter('seller_id', $sellerId);
            if ($searchResult->getSize() == 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('no result found')
                );
            } else {
                $this->collectionProcessor->process($criteria, $searchResult);
                $searchResult->setSearchCriteria($criteria);
                $returnArray = $searchResult->toArray();
                $returnArray['status'] = 1;
                return ["results" => $returnArray];
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['error'] = $e->getMessage();
            $returnArray['status'] = 2;
            return ["results" => $returnArray];
        } catch (\Exception $e) {
            $returnArray['error'] = __('invalid request');
            $returnArray['status'] = 0;
            return ["results" => $returnArray];
        }
    }
}
