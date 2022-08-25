<?php
namespace Mpx\TimeDelivery\Plugin\Account;

class Save
{

    /**
     * Change param timedelivery action save account
     *
     * @param \Webkul\MpTimeDelivery\Controller\Account\Save $subject
     *
     * @return null
     */
    public function beforeExecute(\Webkul\MpTimeDelivery\Controller\Account\Save $subject)
    {
        if ($subject->getRequest()->isPost()) {
            $timeSlotData = $subject->getRequest()->getParam('timedelivery');
            if (isset($timeSlotData['slot'])) {
                foreach ($timeSlotData['slot'] as $key => $value) {
                    $timeSlotData['slot'][$key]['order_count'] = 99999999;
                }
            }
            $subject->getRequest()->setParam('timedelivery', $timeSlotData);
        }
        return null;
    }
}
