<?php
/**
 * Mpx Software
 *
 * @category  Mpx
 * @package   Mpx_Marketplace
 * @author    Mpx
 */

namespace Mpx\Marketplace\Model\ResourceModel\Saleslist;

/**
 * Mpx Marketplace ResourceModel Saleslist collection
 */
class Collection extends \Webkul\Marketplace\Model\ResourceModel\Saleslist\Collection
{

    /**
     * @inheritdoc
     */
    public function getSellerOrderTotalsQuery($idsSelect)
    {
        $salesOrderItem = $this->getTable('sales_order_item');

        $idsSelect->join(
            $salesOrderItem.' as soi',
            'main_table.order_item_id = soi.item_id AND main_table.order_id = soi.order_id',
            [
                'item_id' => 'item_id',
                'SUM(soi.row_total) AS magepro_price'
            ]
        );

        $marketplaceOrders = $this->getTable('marketplace_orders');
        $idsSelect->joinLeft(
            $marketplaceOrders.' as mo',
            'main_table.order_id = mo.order_id and main_table.seller_id = mo.seller_id',
            [
                'tax_to_seller' => 'tax_to_seller',
                'coupon_amount' => 'coupon_amount',
                'shipping_charges' => 'shipping_charges',
                'refunded_shipping_charges' => 'refunded_shipping_charges'
            ]
        );
        $idsSelect->columns(
            [
                'main_table.currency_rate AS currency_rate',
                'main_table.order_id AS order_id',
                'SUM(main_table.total_amount) AS total_amount',
                'SUM(main_table.actual_seller_amount) AS total',
                'SUM(main_table.actual_seller_amount) AS actual_seller_amount',
                'SUM(main_table.total_commission) AS total_commission',
                'SUM(main_table.applied_coupon_amount) AS applied_coupon_amount',
                'SUM(main_table.total_tax) AS total_tax'
            ]
        );
        return $idsSelect;
    }
}
