<?php

namespace Mpx\Marketplace\Block\Product;

/**
 * Class Product Create
 */
class Create extends \Webkul\Marketplace\Block\Product\Create
{
    const SKU_PREFIX_LENGTH = 4;

    /**
     * Get Sku Format
     *
     * @param string $sku
     * @return false|string
     */
    public function getUnformattedSku($sku)
    {
        return substr($sku, self::SKU_PREFIX_LENGTH);
    }
}
