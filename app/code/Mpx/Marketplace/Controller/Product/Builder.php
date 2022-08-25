<?php

namespace Mpx\Marketplace\Controller\Product;

use Magento\Catalog\Model\Product;

/**
 * Mpx Marketplace Product Builder Controller Class.
 */
class Builder extends \Webkul\Marketplace\Controller\Product\Builder
{

    /**
     * Build product based on requestData.
     *
     * @param $requestData
     *
     * @return \Magento\Catalog\Model\Product $mageProduct
     */
    public function build($requestData, $store = 0)
    {
        if (!empty($requestData['id'])) {
            $mageProductId = (int) $requestData['id'];
        } else {
            $mageProductId = '';
        }
        /** @var $mageProduct \Magento\Catalog\Model\Product */
        $mageProduct = $this->_productFactory->create();
        if (!empty($requestData['set'])) {
            $mageProduct->setAttributeSetId($requestData['set']);
        }
        if (!empty($requestData['type'])) {
            $mageProduct->setTypeId($requestData['type']);
        }
        $mageProduct->setStoreId($store);
        if ($mageProductId) {
            try {
                $isPartner = $this->_helper->isSeller();
                $flag = false;
                if ($isPartner == 1) {
                    $rightseller = $this->_helper->isRightSeller($mageProductId);
                    if ($rightseller) {
                        $flag = true;
                    }
                }
                if ($flag) {
                    $mageProduct->load($mageProductId);
                }
                if (!empty($requestData['type'])) {
                    $mageProduct->setTypeId($requestData['type']);
                }
            } catch (\Exception $e) {
                $this->_helper->logDataInLogger(
                    "Controller_Product_Builder execute : ".$e->getMessage()
                );
            }
        }
        if (!$this->_registry->registry('product')) {
            $this->_registry->register('product', $mageProduct);
        }
        if (!$this->_registry->registry('current_product')) {
            $this->_registry->register('current_product', $mageProduct);
        }
        return $mageProduct;
    }
}
