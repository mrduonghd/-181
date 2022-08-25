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
namespace Webkul\MpTimeDelivery\Model;

use Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Webkul\MpTimeDelivery\Model\ResourceModel\TimeSlotConfig as SlotConfig;

/**
 * Time Slot Config Model
 */
class TimeSlotConfig extends AbstractModel implements TimeslotConfigInterface, IdentityInterface
{
    /**
     * Time Slot Config cache tag
     */
    const CACHE_TAG = 'time_slot_config';

    /**
     * Time Slot Config's statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * @var string
     */
    protected $_cacheTag = 'time_slot_config';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'time_slot_config';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(SlotConfig::class);
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getIdentifier()];
    }

    /**
     * Retrieve item id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Get Seller ID
     *
     * @return int|null
     */
    public function getSellerId()
    {
        return $this->getData(self::SELLER_ID);
    }

    /**
     * Get Delivery Day
     *
     * @return int|null
     */
    public function getDeliveryDay()
    {
        return $this->getData(self::DELIVERY_DAY);
    }

    /**
     * Get Start Time
     *
     * @return int|null
     */
    public function getStartTime()
    {
        return $this->getData(self::START_TIME);
    }

    /**
     * Get End Time
     *
     * @return int|null
     */
    public function getEndTime()
    {
        return $this->getData(self::END_TIME);
    }

    /**
     * Get Order Count
     *
     * @return int|null
     */
    public function getOrderCount()
    {
        return $this->getData(self::ORDER_COUNT);
    }

    /**
     * Set ID
     *
     * @return \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Set Seller ID
     *
     * @return \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    /**
     * Set Delivery Date
     *
     * @return \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface
     */
    public function setDeliveryDay($day)
    {
        return $this->setData(self::DELIVERY_DAY, $day);
    }

    /**
     * Set Start Time
     *
     * @return \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface
     */
    public function setStartTime($start)
    {
        return $this->setData(self::START_TIME, $start);
    }

    /**
     * Set End Time
     *
     * @return \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface
     */
    public function setEndTime($end)
    {
        return $this->setData(self::END_TIME, $end);
    }

    /**
     * Set Order Count
     *
     * @return \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface
     */
    public function setOrderCount($quotas)
    {
        return $this->setData(self::ORDER_COUNT, $quotas);
    }
}
