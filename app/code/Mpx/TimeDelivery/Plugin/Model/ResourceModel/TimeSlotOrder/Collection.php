<?php
/**
 * Mpx Software
 *
 * @category  Mpx
 * @package   Mpx_MpTimeDelivery
 * @author    Mpx
 */
namespace Mpx\TimeDelivery\Plugin\Model\ResourceModel\TimeSlotOrder;

use Webkul\MpTimeDelivery\Model\ResourceModel\TimeSlotOrder\Collection as TimeSlotCollection;

/**
 * Mpx MpTimeDelivery Plugin ResourceModel Seller collection
 */
class Collection
{
    /**
     * Plugin addFilterToMap created_at
     *
     * @param TimeSlotCollection $subject
     * @param TimeSlotCollection $result
     * @return TimeSlotCollection
     * @SuppressWarnings(PHPMD.UnusedFormalParameters)
     */
    public function afterGetDeliveryOrderCollection(
        TimeSlotCollection $subject,
        TimeSlotCollection $result
    ) {
        $result->addFilterToMap('created_at', 'sales.created_at');

        return $result;
    }
}
