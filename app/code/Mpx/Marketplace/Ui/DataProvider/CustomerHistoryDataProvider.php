<?php
/**
 * Vnext Software.
 */
namespace Mpx\Marketplace\Ui\DataProvider;

use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\Collection as OrderColl;
use Webkul\Marketplace\Helper\Data as HelperData;

/**
 * @inheritdoc
 */
class CustomerHistoryDataProvider extends \Webkul\Marketplace\Ui\DataProvider\CustomerHistoryDataProvider
{
    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param OrderColl $orderColl
     * @param CollectionFactory $collectionFactory
     * @param HelperData $helperData
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        OrderColl $orderColl,
        CollectionFactory $collectionFactory,
        HelperData $helperData,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $orderColl,
            $collectionFactory,
            $helperData,
            $meta,
            $data
        );
        $sellerId = $helperData->getCustomerId();

        $customerGridFlat = $orderColl->getTable('customer_grid_flat');
        $salesOrder = $orderColl->getTable('sales_order');
        $collectionData = $collectionFactory->create()
        ->addFieldToFilter('seller_id', $sellerId);

        $collectionData->getSelect()
        ->columns('SUM(actual_seller_amount) AS customer_base_total')
        ->columns('count(distinct(order_id)) AS order_count')
        ->columns('order.base_currency_code')
        ->group('magebuyer_id');

        $collectionData->getSelect()->join(
            $customerGridFlat.' as cgf',
            'main_table.magebuyer_id = cgf.entity_id',
            [
                'name' => 'name',
                'email' => 'email',
                'billing_telephone' => 'billing_telephone',
                'gender' => 'gender',
                'billing_full' => 'billing_full'
            ]
        );
        $collectionData->getSelect()->join(
            $salesOrder.' as order',
            'main_table.order_id = order.entity_id',
            ['order_currency_code' => 'order_currency_code']
        );

        $this->collection = $collectionData;
    }
}
