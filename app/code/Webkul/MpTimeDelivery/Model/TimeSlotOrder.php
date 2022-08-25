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

use Webkul\MpTimeDelivery\Api\Data\TimeSlotOrderInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;
use Webkul\MpTimeDelivery\Model\ResourceModel\TimeSlotOrder as SlotOrder;

/**
 * Proerder Complete Item Model
 */
class TimeSlotOrder extends AbstractModel implements TimeSlotOrderInterface, IdentityInterface
{
    /**
     * slots order cache tag
     */
    const CACHE_TAG = 'time_slot_order';

    /**#@-*/
    /**
     * @var string
     */
    protected $_cacheTag = 'time_slot_order';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'time_slot_order';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(SlotOrder::class);
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
     * Get Slot ID
     *
     * @return int|null
     */
    public function getSlotId()
    {
        return $this->getData(self::SLOT_ID);
    }

    /**
     * Get Order ID
     *
     * @return int|null
     */
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * Get selected date
     *
     * @return int|null
     */
    public function getSelectedDate()
    {
        return $this->getData(self::SELECTED_DATE);
    }

    /**
     * Set ID
     *
     * @return \Webkul\MpTimeDelivery\Api\Data\TimeSlotOrderInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Set Order Item ID
     *
     * @return \Webkul\MpTimeDelivery\Api\Data\TimeSlotOrderInterface
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    /**
     * Set Slot ID
     *
     * @return \Webkul\MpTimeDelivery\Api\Data\TimeSlotOrderInterface
     */
    public function setSlotId($id)
    {
        return $this->setData(self::SLOT_ID, $id);
    }

    /**
     * Set Order ID
     *
     * @return \Webkul\MpTimeDelivery\Api\Data\TimeSlotOrderInterface
     */
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }

    /**
     * Set selected date
     *
     * @return \Webkul\MpTimeDelivery\Api\Data\TimeSlotOrderInterface
     */
    public function setSelectedDate($date)
    {
        return $this->setData(self::SELECTED_DATE, $date);
    }
}
