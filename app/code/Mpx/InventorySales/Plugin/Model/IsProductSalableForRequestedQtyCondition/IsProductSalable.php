<?php

namespace Mpx\InventorySales\Plugin\Model\IsProductSalableForRequestedQtyCondition;

use Magento\InventorySalesApi\Api\Data\ProductSalableResultInterfaceFactory;
use Magento\InventorySales\Model\IsProductSalableForRequestedQtyCondition\IsProductSalableForRequestedQtyConditionChain;

class IsProductSalable
{
    /**
     * @var ProductSalableResultInterfaceFactory
     */
    private $productSalableResultFactory;

    /**
     * @param ProductSalableResultInterfaceFactory $productSalableResultFactory
     */
    public function __construct(
        ProductSalableResultInterfaceFactory $productSalableResultFactory
    ) {
        $this->productSalableResultFactory = $productSalableResultFactory;
    }

    /**
     * Disable validation stock Inventory
     *
     * @param IsProductSalableForRequestedQtyConditionChain $subject
     * @param IsProductSalableForRequestedQtyConditionChain $result
     * @return \Magento\InventorySalesApi\Api\Data\ProductSalableResultInterface
     */
    public function afterExecute(
        IsProductSalableForRequestedQtyConditionChain $subject,
        $result
    ) {
        return $this->productSalableResultFactory->create(['errors' => []]);
    }
}
