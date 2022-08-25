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
namespace Webkul\MpTimeDelivery\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\RequestInterface;
use Magento\Config\Model\ResourceModel\Config;
use Webkul\MpTimeDelivery\Model\TimeSlotConfigFactory;
use Webkul\MpTimeDelivery\Helper\Data;

class SaveTimeSlot implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var Config
     */
    protected $resourceConfig;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @param Config $resourceConfig
     * @param Data $helper
     * @param RequestInterface $request
     * @param TimeSlotConfigFactory $timeSlotConfigFactory
     */
    public function __construct(
        Config $resourceConfig,
        Data $helper,
        RequestInterface $request,
        TimeSlotConfigFactory $timeSlotConfigFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->request = $request;
        $this->resourceConfig = $resourceConfig;
        $this->helper = $helper;
        $this->timeSlotConfigFactory = $timeSlotConfigFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * Update order amount
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $timeSlotConfig = $this->timeSlotConfigFactory->create();
        $params = $this->request->getParams();
        if (isset($params['groups']['configurations']['fields']['slots_data']['timedelivery']['slot'])) {
            
            $timeSlots = $params['groups']['configurations']['fields']['slots_data']['timedelivery']['slot'];

            foreach ($timeSlots as $slot) {
                $timeSlotConfig = $this->timeSlotConfigFactory->create();
                if ($slot['is_delete'] != 1) {
                    if (!empty($slot['entity_id'])) {
                        $timeSlotConfig->load($slot['entity_id']);
                    }
                    $timeSlotConfig->setSellerId('0');
                    $timeSlotConfig->setDeliveryDay($slot['delivery_day']);
                    $timeSlotConfig->setStartTime($slot['start_time']);
                    $timeSlotConfig->setEndTime($slot['end_time']);
                    $timeSlotConfig->setOrderCount($slot['order_count']);
                    
                    $this->helper->saveObject($timeSlotConfig);
                } elseif (!empty($slot['entity_id']) && $slot['is_delete'] == 1) {
                    $timeSlotConfig->load($slot['entity_id']);
                    $this->helper->deleteObject($timeSlotConfig);
                }
            }
        } elseif ($params['groups']['configurations']['fields']['active']) {
            $this->messageManager->addWarning(__("Please add delivery time slots!"));
        }
    }
}
