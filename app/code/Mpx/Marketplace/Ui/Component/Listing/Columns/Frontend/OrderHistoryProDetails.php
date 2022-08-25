<?php
/**
 * Mpx Software.
 *
 * @category  Mpx
 * @package   Mpx_Marketplace
 * @author    Mpx
 */

namespace Mpx\Marketplace\Ui\Component\Listing\Columns\Frontend;

/**
 * @inheritdoc
 */
class OrderHistoryProDetails extends \Webkul\Marketplace\Ui\Component\Listing\Columns\Frontend\OrderHistoryProDetails
{
    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $taxToSeller = $this->_helper->getConfigTaxManage();
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                // calculate order actual_seller_amount in base currency
                $appliedCouponAmount = $item['applied_coupon_amount'];
                $taxToSeller = $item['tax_to_seller'];
                $shippingamount = $item['shipping_charges'];
                $refundedShippingAmount = $item['refunded_shipping_charges'];
                $totalshipping = $shippingamount - $refundedShippingAmount;
                $taxAmount = $item['total_tax'];
                $commissionFee = $item['total_commission'];
                $vendorTaxAmount = 0;
                if ($taxToSeller) {
                    $vendorTaxAmount = $taxAmount;
                }
                else {
                    $vendorTaxAmount = $taxAmount + $commissionFee;
                }
                if ($item['actual_seller_amount'] * 1) {
                    $taxShippingTotal = $vendorTaxAmount + $totalshipping - $appliedCouponAmount;
                    $item['actual_seller_amount'] = $item['actual_seller_amount'] + $taxShippingTotal;
                } else {
                    if ($totalshipping * 1) {
                        $item['actual_seller_amount'] = $totalshipping - $appliedCouponAmount;
                    }
                }
                // calculate order total in ordered currency
                $order = $this->orderRepository->get($item['order_id']);

                $item['purchased_actual_seller_amount'] = $item['currency_rate'] * $item['actual_seller_amount'];

                // Updated product name
                $item['magepro_name'] = $this->getpronamebyorder(
                    $item['order_id'],
                    $item['seller_id']
                );
            }
        }

        return $dataSource;
    }
}
