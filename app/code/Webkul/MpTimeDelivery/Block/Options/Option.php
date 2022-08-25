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
namespace Webkul\MpTimeDelivery\Block\Options;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Webkul\MpTimeDelivery\Model\Config\Source\Days;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Webkul\MpTimeDelivery\Model\ResourceModel\TimeSlotConfig\CollectionFactory;
use Magento\Framework\View\Element\Html\Select;
use Webkul\MpTimeDelivery\Helper\Data;

class Option extends Template
{
    /**
     * @var string
     */
    protected $_template = 'account/options/option.phtml';

    /**
     * @var Webkul\MpTimeDelivery\Model\ResourceModel\TimeSlotConfig\CollectionFactory
     */
    protected $timeSlotCollection;
    
    /**
     * @var Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;
    
    /**
     * @var \Webkul\MpTimeDelivery\Model\Config\Source\Days
     */
    protected $days;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @var Webkul\MpTimeDelivery\Helper\Data
     */
    protected $helper;
    
    /**
     * @var int
     */
    protected $itemCount = 1;

    /**
     * @param Context                   $context
     * @param CollectionFactory         $timeSlotCollection
     * @param SessionFactory            $customerSessionFactory
     * @param Days                      $days
     * @param DateTime                  $dateTime
     * @param Data                      $helper
     * @param array                     $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $timeSlotCollection,
        SessionFactory $customerSessionFactory,
        Days $days,
        DateTime $dateTime,
        Data $helper,
        array $data = []
    ) {
        $this->timeSlotCollection = $timeSlotCollection;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->days = $days;
        $this->dateTime = $dateTime;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Return Item Count
     *
     * @return int
     */
    public function getItemCount()
    {
        return $this->itemCount;
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
     * Retrieve options field name prefix
     *
     * @return string
     */
    public function getFieldName()
    {
        return 'timedelivery[slot]';
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
     * Provide already save values
     *
     * @return array
     */
    public function getTimeSlotsValue()
    {
        $collection = $this->timeSlotCollection->create()
                        ->addFieldToFilter(
                            'seller_id',
                            ['eq' => $this->getCurrentCustomerId()]
                        );
        $values = [];
        if ($collection->getSize()) {
            foreach ($collection as $slot) {
                $value = [];
                $value['id'] = $slot->getEntityId();
                $value['entity_id'] = $slot->getEntityId();
                $value['item_count'] = 1;
                $value['seller_id'] = $this->getCurrentCustomerId();
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
     * Return current customer id
     *
     * @return null|int
     */
    public function getCurrentCustomerId()
    {
        return $this->customerSessionFactory->create()->getCustomerId();
    }
}
