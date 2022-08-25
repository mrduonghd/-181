<?php

namespace Mpx\Marketplace\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use Magento\Shipping\Model\Carrier\CarrierInterface;

/**
 *  shipping AbstractCarrier
 */
class AbstractCarrier extends AbstractCarrierOnline implements CarrierInterface
{
    /**
     * Collect rates
     *
     * @param RateRequest $request
     * @return false
     */
    public function collectRates(RateRequest $request)
    {
        return false;
    }

    /**
     * Allow medthod shipping
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * Do shipment request to carrier web service, obtain Print Shipping Labels and process errors in response
     *
     * @param \Magento\Framework\DataObject $request
     * @return \Magento\Framework\DataObject
     */
    protected function _doShipmentRequest(\Magento\Framework\DataObject $request)
    {
        $result = new \Magento\Framework\DataObject();
        return $result;
    }

    /**
     * Get tracking information
     *
     * @param string $tracking
     * @return string|false
     * @api
     */
    public function getTrackingInfo($tracking)
    {
        $trackingInfo = $this->_trackStatusFactory->create();

        $trackingInfo->setData([
            'carrier' => $this->_code,
            'carrier_title' => $this->getConfigData('title'),
            'tracking' => $tracking,
        ]);
        return $trackingInfo;
    }
}
