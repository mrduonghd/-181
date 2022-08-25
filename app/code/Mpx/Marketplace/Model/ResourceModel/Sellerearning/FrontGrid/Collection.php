<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Mpx\Marketplace\Model\ResourceModel\Sellerearning\FrontGrid;

/**
 * Mpx\Marketplace\Model\ResourceModel\Saleslist\Grid\Collection Class
 * Collection for displaying grid of marketplace Saleslist.
 */
class Collection extends \Webkul\Marketplace\Model\ResourceModel\Sellerearning\FrontGrid\Collection
{
    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        try {
            $from = null;
            $to = null;
            $paramData = $this->helperData->getParams();
            $this->updatePeriodFormat();
            $filterDateFrom = $paramData['from'] ?? '';
            $filterDateTo = $paramData['to'] ?? '';
            if ($filterDateTo) {
                $todate = date_create($filterDateTo);
                $to = date_format($todate, 'Y-m-d 23:59:59');
            }
            if (!$to) {
                $to = date('Y-m-d 23:59:59');
            }
            if ($filterDateFrom) {
                $fromdate = date_create($filterDateFrom);
                $from = date_format($fromdate, 'Y-m-d H:i:s');
            }
            if (!$from) {
                $from = date('Y-m-d 23:59:59', strtotime($from));
            }
            $sellerId = $this->helperData->getCustomerId();
            $this->getSelect()->where("main_table.seller_id = ".$sellerId);
            if ($from && $to) {
                $this->getSelect()->where(
                    "main_table.created_at BETWEEN '".$from."' AND '".$to."'"
                );
            }

            $this->getSelect()->reset(\Zend_Db_Select::COLUMNS);
            $salesOrder = $this->getTable('sales_order');
            $this->getSelect()->join(
                $salesOrder.' as order',
                'main_table.order_id = order.entity_id',
                ['order_currency_code' => 'order_currency_code']
            );
            $this->getSelect()->columns(
                [
                    'COUNT(DISTINCT order_id) as order_count',
                    'SUM(order_item_id) as item_count',
                    'SUM(total_commission) as total_commission',
                    'SUM(actual_seller_amount - applied_coupon_amount) as total_seller_amount',
                    'SUM(applied_coupon_amount) as total_discount_amount',
                    'SUM(total_tax) as total_tax_amount',
                    'SUM(total_amount) as total_amount',
                    'order.base_currency_code',
                    'created_at'
                ]
            );
            $this->getSelect()->group($this->_periodFormat);
        } catch (\Exception $e) {
            $sellerId = $this->helperData->getCustomerId();
            $this->getSelect()->where("main_table.seller_id = ".$sellerId."
            AND main_table.order_id = 0");
            $this->getSelect()->reset(\Zend_Db_Select::COLUMNS);
            $salesOrder = $this->getTable('sales_order');
            $this->getSelect()->join(
                $salesOrder.' as order',
                'main_table.order_id = order.entity_id',
                ['order_currency_code' => 'order_currency_code']
            );
            $this->getSelect()->columns(
                [
                    'COUNT(DISTINCT order_id) as order_count',
                    'SUM(order_item_id) as item_count',
                    'SUM(total_commission) as total_commission',
                    'SUM(actual_seller_amount - applied_coupon_amount) as total_seller_amount',
                    'SUM(applied_coupon_amount) as total_discount_amount',
                    'SUM(total_tax) as total_tax_amount',
                    'SUM(total_amount) as total_amount',
                    'order.base_currency_code',
                    'created_at'
                ]
            );
            $this->getSelect()->group($this->_periodFormat);
            $this->helperData->logDataInLogger("Block_Product_ProductList getAllProducts : ".$e->getMessage());
        }
    }

    /**
     * updatePeriodFormat function
     *
     * @return void
     */
    protected function updatePeriodFormat()
    {
        $paramData = $this->helperData->getParams();
        $this->_period = $paramData['period'] ?? '';
        $connection = $this->getConnection();
        if ('month' == $this->_period) {
            $this->_periodFormat = $connection->getDateFormatSql('main_table.created_at', '%Y-%m');
        } elseif ('year' == $this->_period) {
            $this->_periodFormat = $connection->getDateExtractSql(
                'main_table.created_at',
                \Magento\Framework\DB\Adapter\AdapterInterface::INTERVAL_YEAR
            );
        } else {
            $this->_periodFormat = $connection->getDateFormatSql('main_table.created_at', '%Y-%m-%d');
        }
    }
}
