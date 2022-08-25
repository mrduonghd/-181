<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpTimeDelivery
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpTimeDelivery\Api\Data;

interface TimeslotConfigInterface
{
    const ENTITY_ID             = 'id';
    const SELLER_ID             = 'seller_id';
    const DELIVERY_DAY          = 'delivery_day';
    const START_TIME            = 'start_time';
    const END_TIME              = 'end_time';
    const ORDER_COUNT           = 'order_count';
    
    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get Seller ID
     *
     * @return int|null
     */
    public function getSellerId();

    /**
     * Get Delivery Day
     *
     * @return int|null
     */
    public function getDeliveryDay();

    /**
     * Get Start Time
     *
     * @return int|null
     */
    public function getStartTime();

    /**
     * Get End Time
     *
     * @return int|null
     */
    public function getEndTime();

    /**
     * Get Order Count
     *
     * @return int|null
     */
    public function getOrderCount();

    /**
     * Set ID
     *
     * @return \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface
     */
    public function setId($id);

    /**
     * Set Seller ID
     *
     * @return \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface
     */
    public function setSellerId($sellerId);

    /**
     * Set Delivery Day
     *
     * @return \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface
     */
    public function setDeliveryDay($day);

    /**
     * Set Start Time
     *
     * @return \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface
     */
    public function setStartTime($start);

    /**
     * Set End Time
     *
     * @return \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface
     */
    public function setEndTime($end);

    /**
     * Set Order Count
     *
     * @return \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface
     */
    public function setOrderCount($quotas);
}
