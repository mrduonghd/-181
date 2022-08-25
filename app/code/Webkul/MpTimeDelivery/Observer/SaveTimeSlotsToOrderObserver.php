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

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManager;

class SaveTimeSlotsToOrderObserver implements ObserverInterface
{
    /**
     * @var Magento\Framework\Session\SessionManager
     */
    protected $_coreSession;

    /**
     * @var \Webkul\MpTimeDelivery\Helper\Data
     */
    protected $_helper;

    /**
     * @param SessionManager                        $coreSession
     * @param \Webkul\MpTimeDelivery\Helper\Data    $helper
     */
    public function __construct(
        SessionManager $coreSession,
        \Webkul\MpTimeDelivery\Helper\Data $helper
    ) {
        $this->_coreSession = $coreSession;
        $this->_helper = $helper;
    }

    /**
     * @param  EventObserver $observe
     */
    public function execute(EventObserver $observer)
    {
        if ($this->_helper->getConfigData('active')) {
            $order = $observer->getOrder();
            $sellerData = $this->_coreSession->getSellerSlotInfo();
            if ($sellerData) {
                foreach ($order->getAllItems() as $item) {
                    if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                        continue;
                    }
                    $mpassignproductId = $this->_helper->getAssignProduct($item);
                    $sellerId = $this->_helper->getSellerId($mpassignproductId, $item->getProductId());
                    foreach ($sellerData as $value) {
                        if ($value['id'] == 0) {
                            $item->setDeliveryDate($value['date']);
                            $item->setDeliveryTime($value['slot_time']);
                        }elseif ($sellerId == $value['id']) {
                            $item->setDeliveryDate($value['date']);
                            $item->setDeliveryTime($value['slot_time']);
                        }
                    }
                }
            }
        }
        return $this;
    }
}
