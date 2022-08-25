<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MpTimeDelivery
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\MpTimeDelivery\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Checkout\Model\CompositeConfigProvider;
use Webkul\MpTimeDelivery\Model\Seller\OrderConfigProviders;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Theme\Block\Html\Pager;

class Orders extends Template
{
    /**
     * @var \Magento\Checkout\Model\CompositeConfigProvider
     */
    protected $configProvider;

    /**
     * @var \Webkul\MpTimeDelivery\Model\Seller\OrderConfigProviders
     */
    protected $orderConfigProvider;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var array
     */
    protected $layoutProcessors;

    /**
     * @var object
     */
    protected $collection;

    /**
     * @param Context                   $context
     * @param CompositeConfigProvider   $configProvider
     * @param OrderConfigProviders      $orderConfigProvider
     * @param DateTime                  $date
     * @param array                     $layoutProcessors
     * @param array                     $data
     */
    public function __construct(
        Context $context,
        CompositeConfigProvider $configProvider,
        OrderConfigProviders $orderConfigProvider,
        DateTime $date,
        array $layoutProcessors = [],
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
        $this->layoutProcessors = $layoutProcessors;
        $this->orderConfigProvider = $orderConfigProvider;
        $this->date = $date;
    }

    /**
     * @return string
     */
    public function getJsLayout()
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }
        return parent::getJsLayout();
    }

    /**
     * Get Order Collection for Seller Time Slots
     *
     * @return \Webkul\MpTimeDelivery\Model\TimeSlotOrder
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getOrderCollection()
    {
        $paramData = $this->getRequest()->getParams();
        $orderId = '';
        $filterDateFrom = '';
        $filterDateTo = '';
        $from = null;
        $to = null;
        $totime = null;
        $fromtime = null;
        if (isset($paramData['s'])) {
            $orderId = $paramData['s'] != '' ? $paramData['s'] : '';
        }
        if (isset($paramData['from_date'])) {
            $filterDateFrom = $paramData['from_date'] != '' ? $paramData['from_date'] : '';
        }
        if (isset($paramData['to_date'])) {
            $filterDateTo = $paramData['to_date'] != '' ? $paramData['to_date'] : '';
        }
        if ($filterDateTo) {
            $todate = date_create($filterDateTo);
            $to = date_format($todate, 'Y-m-d 23:59:59');
        }
        if (!$to) {
            $to = date('Y-m-d 23:59:59');
        }
        if ($filterDateFrom) {
            $fromdate = date_create($filterDateFrom);
            $from = date_format($fromdate, 'Y-m-d H:i:s');
        }
        $collection = $this->orderConfigProvider->getCollection();
        if ($orderId) {
            $collection->addFieldToFilter(
                'increment_id',
                ['eq' => $orderId]
            );
        }
        if ($from && $to) {
            $collection->addFieldToFilter(
                'selected_date',
                ['datetime' => true, 'from' => $from, 'to' => $to]
            );
        }
        if (!$this->collection) {
            $this->collection = $collection;
        }
        return $this->collection;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getOrderCollection()) {
            $pager = $this->getLayout()->createBlock(
                Pager::class,
                'deliveryorder.news.pager'
            )->setAvailableLimit([5=>5,10=>10,15=>15])->setShowPerPage(true)->setCollection(
                $this->getOrderCollection()
            );
            $this->setChild('pager', $pager);
            $this->getOrderCollection()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * retrun current date
     * @param  string
     * @return string
     */
    public function getDate($date)
    {
        return $this->date->gmtDate('l, j F, Y', $date);
    }

    /**
     * merge from slot to To Slot time
     * @param  \Webkul\MpTimeDelivery\Model\TimeSlotOrder $order
     * @return string
     */
    public function getSlot($order)
    {
        return $this->date->gmtDate('h:i A', $order->getStartTime()).'-'.
            $this->date->gmtDate('h:i A', $order->getEndTime());
    }
}
