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
 namespace Webkul\MpApi\Model\Admin;

 use Webkul\MpApi\Model\ResourceModel\Seller\CollectionFactory;
 use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
 use Magento\Framework\App\ObjectManager;

class AdminSellerRepositry implements \Webkul\MpApi\Api\AdminSellerManagementInterface
{
    /**
     * @var Data\SellerResultsInterface
     */
    protected $sellerResultsFactory;

    /**
     * @var SellerlistCollectionFactory
     */
    protected $_sellerlistCollectionFactory;

    /**
     * @param Data\SellerResultsInterface $sellerResultsFactory
     * @param SaleslistCollectionFactory $saleslistCollectionFactory
     */
    public function __construct(
        CollectionProcessorInterface $collectionProcessor = null,
        CollectionFactory $sellerlistCollectionFactory
    ) {
        $this->collectionProcessor = $collectionProcessor ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class);
        $this->_sellerlistCollectionFactory = $sellerlistCollectionFactory;
    }
     
    /**
     * @inheritDoc
     */
    public function getSellerList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        try {
            $searchResult = $this->_sellerlistCollectionFactory
                ->create()
                ->addFieldToSelect(
                    '*'
                )
                ->setOrder(
                    'entity_id',
                    'desc'
                );
                $this->collectionProcessor->process($criteria, $searchResult);
                $searchResult->setSearchCriteria($criteria);
            if ($searchResult->getSize() == 0) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('no sellers found')
                );
            }
            $returnArray = $searchResult->toArray();
            $returnArray['status'] = 1;
            return ["results" => $returnArray];
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
