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
namespace Webkul\MpTimeDelivery\Block\Adminhtml;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Webkul\MpTimeDelivery\Model\Config\Source\Days;
use Webkul\MpTimeDelivery\Model\Config\Source\Hours;
use Webkul\MpTimeDelivery\Model\Config\Source\Minutes;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Webkul\MpTimeDelivery\Helper\Data;
use Webkul\MpTimeDelivery\Model\ResourceModel\TimeSlotConfig\CollectionFactory;
use Magento\Framework\View\Element\Html\Select;

class Options extends Template
{

    /**
     * @var string
     */
    protected $_template = 'options/option.phtml';
    
    /**
     * @var int
     */
    protected $_itemCount = 1;

    /**
     * @var \Webkul\MpTimeDelivery\Model\Config\Source\Days
     */
    protected $days;

    /**
     * @var \Webkul\MpTimeDelivery\Model\Config\Source\Hours
     */
    protected $hours;

    /**
     * @var \Webkul\MpTimeDelivery\Model\Config\Source\Minutes
     */
    protected $minutes;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var Webkul\MpTimeDelivery\Helper\Data
     */
    protected $helper;

     /**
      * @var \Webkul\MpTimeDelivery\Model\ResourceModel\TimeSlotConfig\CollectionFactory
      */
    protected $timeSlotCollection;

    /**
     * @param Context               $context
     * @param Days                  $days
     * @param Hours                 $hours
     * @param Minutes               $minutes
     * @param DateTime              $dateTime
     * @param Data                  $helper
     * @param CollectionFactory     $timeSlotCollection
     * @param array                 $data
     */
    public function __construct(
        Context $context,
        Days $days,
        Hours $hours,
        Minutes $minutes,
        DateTime $dateTime,
        Data $helper,
        CollectionFactory $timeSlotCollection,
        array $data = []
    ) {
        $this->days = $days;
        $this->hours = $hours;
        $this->minutes = $minutes;
        $this->dateTime = $dateTime;
        $this->helper = $helper;
        $this->timeSlotCollection = $timeSlotCollection;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve options field id prefix
     *
     * @return string
     */
    public function getFieldId()
    {
        return 'time_delivery';
    }

    /**
     * Return Item Count
     *
     * @return int
     */
    public function getItemCount()
    {
        return $this->_itemCount;
    }

    /**
     * Retrieve options field name prefix
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'groups[configurations][fields][slots_data][timedelivery][slot]';
    }

    /**
     * Retrieve HTML for Days
     *
     * @return mixed
     */
    public function getDaysHtml()
    {
        $select = $this->getLayout()->createBlock(
            Select::class
        )->setData(
            [
                'id' => $this->getFieldId() . '_<%- data.id %>_type',
                'class' => 'select select-days-type required-option-select',
            ]
        )->setName(
            $this->getFieldName() . '[<%- data.id %>][delivery_day]'
        )->setOptions(
            $this->days->toOptionArray()
        );

        return $select->getHtml();
    }

    /**
     * Retrieve HTML for Hours
     *
     * @param string $type
     *
     * @return mixed
     */
    public function getHourHtml($type)
    {
        $select = $this->getLayout()->createBlock(
            Select::class
        )->setData(
            [
                'id' => $type.'_'.$this->getFieldId() . '_<%- data.id %>_hh',
                'class' => 'select select-minute-type required-option-select',
                'style'=> 'width:80px',
            ]
        )->setName(
            $this->getFieldName() . '[<%- data.id %>]['.$type.'][hh]'
        )->setOptions(
            $this->hours->toOptionArray()
        );

        return $select->getHtml();
    }

    /**
     * Retrieve HTML for Minutes
     *
     * @param string $type
     *
     * @return mixed
     */
    public function getMinuteHtml($type)
    {
        $select = $this->getLayout()->createBlock(
            Select::class
        )->setData(
            [
                'id' => $type.'_'.$this->getFieldId() . '_<%- data.id %>_mm',
                'class' => 'select select-minute-type required-option-select',
                'style'=> 'width:80px',
            ]
        )->setName(
            $this->getFieldName() . '[<%- data.id %>]['.$type.'][mm]'
        )->setOptions(
            $this->minutes->toOptionArray()
        );

        return $select->getHtml();
    }

    /**
     * Provide already save values
     *
     * @return array
     */
    public function getTimeSlotsValue()
    {
        $collection = $this->timeSlotCollection->create()
            ->addFieldToFilter('seller_id', ['eq' => 0]);
        $values = [];
        if ($collection->getSize()) {
            foreach ($collection as $slot) {
                $value = [];
                $value['id'] = $slot->getEntityId();
                $value['entity_id'] = $slot->getEntityId();
                $value['item_count'] = 1;
                $value['seller_id'] = 0;
                $value['day'] = $slot->getDeliveryDay();
                $value['start'] = $this->dateTime->gmtDate('h:i A', $slot->getStartTime());
                $value['end'] = $this->dateTime->gmtDate('h:i A', $slot->getEndTime());
                $value['quota'] = $slot->getOrderCount();
                $values[] = $this->helper->getJson()->serialize($value);
            }
        }
        
        return $values;
    }

    /**
     * Get Helper Object
     *
     * @return object
     */
    public function getHelperObject()
    {
        return $this->helper;
    }
}
