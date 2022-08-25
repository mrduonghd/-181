<?php
/**
 * MpshippingDistInterface Interface
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpshipping\Api\Data;

interface MpshippingDistInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID = 'entity_id';
    const PRICE_FROM = 'price_from';
    const PRICE_TO  = 'price_to';
    const DIST_FROM = 'dist_from';
    const DIST_TO   = 'dist_to';
    const PRICE = 'price';
    const PARTNER_ID = 'partner_id';
    const SHIPPING_METHOD_ID = 'shipping_method_id';

    /**
     * Get Entity ID
     *
     * @return int|null
     */
    public function getEntityId();
    /**
     * Get Price From
     *
     * @return float|null
     */
    public function getPriceFrom();
    /**
     * Get Price To
     *
     * @return float|null
     */
    public function getPriceTo();
    /**
     * Get Distance From
     *
     * @return float|null
     */
    public function getDistFrom();
    /**
     * Get Distance To
     *
     * @return float|null
     */
    public function getDistTo();
    /**
     * Get Price
     *
     * @return float|null
     */
    public function getPrice();
    /**
     * Get Seller Id
     *
     * @return int|null
     */
    public function getPartnerId();
    /**
     * Get Shipping Method Id
     *
     * @return int|null
     */
    public function getShippingMethodId();
    /**
     * Set Entity ID
     *
     * @param int $id
     * @return \Webkul\Mpshipping\Api\Data\MpshippingDistInterface
     */
    public function setEntityId($id);
    /**
     * Set Price From
     *
     * @param float $priceFrom
     * @return \Webkul\Mpshipping\Api\Data\MpshippingDistInterface
     */
    public function setPriceFrom($priceFrom);
    /**
     * Set Price To
     *
     * @param float $priceTo
     * @return \Webkul\Mpshipping\Api\Data\MpshippingDistInterface
     */
    public function setPriceTo($priceTo);
    /**
     * Set Distance From
     *
     * @param float $distFrom
     * @return \Webkul\Mpshipping\Api\Data\MpshippingDistInterface
     */
    public function setDistFrom($distFrom);
    /**
     * Set Distance To
     *
     * @param float $distTo
     * @return \Webkul\Mpshipping\Api\Data\MpshippingDistInterface
     */
    public function setDistTo($distTo);
    /**
     * Set Price
     *
     * @param float $price
     * @return \Webkul\Mpshipping\Api\Data\MpshippingDistInterface
     */
    public function setPrice($price);
    /**
     * Set Seller Id
     *
     * @param int $sellerId
     * @return \Webkul\Mpshipping\Api\Data\MpshippingDistInterface
     */
    public function setPartnerId($sellerId);
    /**
     * set Shipping Method Id
     *
     * @param int $methodId
     * @return \Webkul\Mpshipping\Api\Data\MpshippingDistInterface
     */
    public function setShippingMethodId($methodId);
}
