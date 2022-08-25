<?php
/**
 * Mpx Software.
 *
 * @category  Mpx
 * @package   Mpx_Marketplace
 * @author    Mpx
 */

namespace Mpx\Marketplace\Observer;

/**
 * Mpx Marketplace SalesOrderPlaceAfterObserver Observer Model.
 */
class SalesOrderPlaceAfterObserver extends \Webkul\Marketplace\Observer\SalesOrderPlaceAfterObserver
{

    /**
     * Get Seller's Product Data.
     *
     * @param \Magento\Sales\Model\Order $order
     * @param int                        $ratesPerCurrency
     *
     * @return array
     */
    public function getSellerProductData($order, $ratesPerCurrency)
    {
        $lastOrderId = $order->getId();
        /*
        * Get Global Commission Rate for Admin
        */
        $percent = $this->_marketplaceHelper->getConfigCommissionRate();

        $sellerProArr = [];
        $sellerTaxArr = [];
        $sellerCouponArr = [];
        $isShippingFlag = [];

        foreach ($order->getAllItems() as $item) {
            $itemData = $item->getData();
            $sellerId = $this->getSellerIdPerProduct($item);
            $calculationStatus = true;
            if ($itemData['product_type'] == 'bundle') {
                $productOptions = $item->getProductOptions();
                $calculationStatus = $productOptions['product_calculations'] ? true : false;
            }
            if ($calculationStatus) {
                $isShippingFlag = $this->getShippingFlag($item, $sellerId, $isShippingFlag);

                $price = $itemData['base_price'];

                $taxamount = $itemData['base_tax_amount'];
                $qty = $item->getQtyOrdered();

                $totalamount = $item->getRowTotal();

                $advanceCommissionRule = $this->_customerSession->getData(
                    'advancecommissionrule'
                );
                $commission = $this->getCommission($sellerId, $totalamount, $item, $advanceCommissionRule);

                $actparterprocost = $totalamount - $commission;
            } else {
                if (empty($isShippingFlag[$sellerId])) {
                    $isShippingFlag[$sellerId] = 0;
                }
                $price = 0;
                $taxamount = 0;
                $qty = $item->getQtyOrdered();
                $totalamount = 0;
                $commission = 0;
                $actparterprocost = 0;
            }

            $collectionsave = $this->saleslistFactory->create();
            $collectionsave->setMageproductId($item->getProductId());
            $collectionsave->setOrderItemId($item->getItemId());
            $collectionsave->setParentItemId($item->getParentItemId());
            $collectionsave->setOrderId($lastOrderId);
            $collectionsave->setMagerealorderId($order->getIncrementId());
            $collectionsave->setMagequantity($qty);
            $collectionsave->setSellerId($sellerId);
            $collectionsave->setCpprostatus(\Webkul\Marketplace\Model\Saleslist::PAID_STATUS_PENDING);
            $collectionsave->setMagebuyerId($this->_customerSession->getCustomerId());
            $collectionsave->setMageproPrice($price);
            $collectionsave->setMageproName($item->getName());
            if ($totalamount != 0) {
                $collectionsave->setTotalAmount($totalamount);
                $commissionRate = ($commission * 100) / $totalamount;
            } else {
                $collectionsave->setTotalAmount($price);
                $commissionRate = $percent;
            }
            $collectionsave->setTotalTax($taxamount);
            if (!$this->_marketplaceHelper->isSellerCouponModuleInstalled()) {
                if (isset($itemData['base_discount_amount'])) {
                    $baseDiscountAmount = $itemData['base_discount_amount'];
                    $collectionsave->setIsCoupon(1);
                    $collectionsave->setAppliedCouponAmount($baseDiscountAmount);

                    if (!isset($sellerCouponArr[$sellerId])) {
                        $sellerCouponArr[$sellerId] = 0;
                    }
                    $sellerCouponArr[$sellerId] = $sellerCouponArr[$sellerId] + $baseDiscountAmount;
                }
            }
            $collectionsave->setTotalCommission($commission);
            $collectionsave->setActualSellerAmount($actparterprocost);
            $collectionsave->setCommissionRate($commissionRate);
            $collectionsave->setCurrencyRate($ratesPerCurrency);
            if (isset($isShippingFlag[$sellerId])) {
                $collectionsave->setIsShipping($isShippingFlag[$sellerId]);
            }
            $collectionsave->setCreatedAt($this->_date->gmtDate());
            $collectionsave->setUpdatedAt($this->_date->gmtDate());
            $collectionsave->save();
            if (!isset($sellerTaxArr[$sellerId])) {
                $sellerTaxArr[$sellerId] = 0;
            }
            $sellerTaxArr[$sellerId] = $sellerTaxArr[$sellerId] + $taxamount;
            if ($price != 0.0000) {
                if (!isset($sellerProArr[$sellerId])) {
                    $sellerProArr[$sellerId] = [];
                }
                array_push($sellerProArr[$sellerId], $item->getProductId());
            } else {
                if (!$item->getParentItemId()) {
                    if (!isset($sellerProArr[$sellerId])) {
                        $sellerProArr[$sellerId] = [];
                    }
                    array_push($sellerProArr[$sellerId], $item->getProductId());
                }
            }
        }

        return [
            'seller_pro_arr' => $sellerProArr,
            'seller_tax_arr' => $sellerTaxArr,
            'seller_coupon_arr' => $sellerCouponArr
        ];
    }
}
