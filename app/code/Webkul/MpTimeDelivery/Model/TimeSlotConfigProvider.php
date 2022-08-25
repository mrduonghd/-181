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

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Checkout\Model\SessionFactory as CheckoutSessionFactory;
use Webkul\MpTimeDelivery\Model\ResourceModel\TimeSlotConfig\CollectionFactory;
use Webkul\Preorder\Helper\Data;

class TimeSlotConfigProvider implements ConfigProviderInterface
{
    const XPATH_ALLOWED_DAY     = 'timeslot/configurations/allowed_days';
    const XPATH_PROCESS_TIME    = 'timeslot/configurations/process_time';
    const XPATH_MAX_DAYS        = 'timeslot/configurations/maximum_days';
    const ENABLE                = 'timeslot/configurations/active';
    const XPATH_MESSAGE         = 'timeslot/configurations/message';
    const ENABLED               = 'timeslot/configurations/active';
    const TIME_ZONE = 'general/locale/timezone';
    const DEFAULT_START_TIME = 'timeslot/configurations/default_start_time';
    const DEFAULT_END_TIME = 'timeslot/configurations/default_end_time';
    const DEFAULT_DAY = 'timeslot/configurations/default_allowed_day';
    
    /**
     * @var \Magento\Checkout\Model\SessionFactory
     */
    private $checkoutSessionFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Webkul\MpTimeDelivery\Model\ResourceModel\TimeSlotConfig\CollectionFactory
     */
    protected $_timeSlotCollection;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Quote\Model\Quote\Item\OptionFactory
     */
    protected $_itemOptionFactory;

    /**
     * @var \Webkul\Marketplace\Model\ProductFactory
     */
    protected $_mpProductFactory;

    /**
     * @var \Webkul\MpTimeDelivery\Model\TimeSlotOrderFactory
     */
    protected $_timeSlotOrderFactory;

    /**
     * @var \Webkul\MpTimeDelivery\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepositoryInterface;

    /**
     * @param CheckoutSessionFactory $checkoutSessionFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Quote\Model\Quote\Item\OptionFactory $itemOptionFactory
     * @param \Webkul\Marketplace\Model\ProductFactory $mpProductFactory
     * @param \Webkul\MpTimeDelivery\Model\TimeSlotOrderFactory $timeSlotOrderFactory
     * @param \Webkul\MpTimeDelivery\Helper\Data $helper
     * @param CollectionFactory $timeSlotCollection
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        CheckoutSessionFactory $checkoutSessionFactory,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Quote\Model\Quote\Item\OptionFactory $itemOptionFactory,
        \Webkul\Marketplace\Model\ProductFactory $mpProductFactory,
        \Webkul\MpTimeDelivery\Model\TimeSlotOrderFactory $timeSlotOrderFactory,
        \Webkul\MpTimeDelivery\Helper\Data $helper,
        CollectionFactory $timeSlotCollection,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->checkoutSessionFactory = $checkoutSessionFactory;
        $this->quoteRepository = $quoteRepository;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->customerFactory = $customerFactory;
        $this->_date = $date;
        $this->_itemOptionFactory = $itemOptionFactory;
        $this->_mpProductFactory = $mpProductFactory;
        $this->_timeSlotOrderFactory = $timeSlotOrderFactory;
        $this->_helper = $helper;
        $this->_timeSlotCollection = $timeSlotCollection;
        $this->customerRepositoryInterface = $customerRepositoryInterface;
        $this->moduleManager = $moduleManager;
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        $store = $this->getStoreId();
        $allowedDays = $this->scopeConfig->getValue(self::XPATH_ALLOWED_DAY, ScopeInterface::SCOPE_STORE, $store);
        $defaultAllowedDay = $this->scopeConfig->getValue(self::DEFAULT_DAY, ScopeInterface::SCOPE_STORE, $store);
        $defaultStartTime = $this->scopeConfig->getValue(self::DEFAULT_START_TIME, ScopeInterface::SCOPE_STORE, $store);
        $defaultEndTime = $this->scopeConfig->getValue(self::DEFAULT_END_TIME, ScopeInterface::SCOPE_STORE, $store);
        
        $processTime = $this->scopeConfig->getValue(self::XPATH_PROCESS_TIME, ScopeInterface::SCOPE_STORE, $store);
        $maxDays = $this->scopeConfig->getValue(self::XPATH_MAX_DAYS, ScopeInterface::SCOPE_STORE, $store);
        $message = $this->scopeConfig->getValue(self::XPATH_MESSAGE, ScopeInterface::SCOPE_STORE, $store);
        $isEnabled = (bool)$this->scopeConfig->getValue(self::ENABLED, ScopeInterface::SCOPE_STORE, $store);
        $timezone = $this->scopeConfig->getValue(self::TIME_ZONE, ScopeInterface::SCOPE_STORE, $store);
        $sellerIds = $this->getSellerIds();

        $date = strtotime("+".$processTime." day", strtotime(date('Y-m-d')));

        $config = [
            'seller' => [],
            'allowed_days' => explode(',', $allowedDays),
            'process_time' => $processTime,
            'start_date'   => date("Y-m-d", $date),
            'max_days'     => $maxDays,
            'isEnabled'    => $isEnabled,
            'timezone' => $timezone,
            'defaultDay'=>$defaultAllowedDay,
            'defaultStartTime'=>$defaultStartTime,
            'defaultEndTime'=>$defaultEndTime,
        ];

        if (!$isEnabled) {
            return $config;
        }
        $allowedDays = explode(',', $allowedDays);

        $customerCollection = $this->customerFactory->create()
                                ->getCollection()
                                ->addFieldToFilter("entity_id", ["in" => $sellerIds]);

        foreach ($customerCollection as $customer) {
            $sellerId = $customer->getId();
            $minimum_time =$this->customerRepositoryInterface->getById($sellerId)
                    ->getCustomAttribute('minimum_time_required');
            if ($minimum_time) {
                $minimum_time = $minimum_time->getValue();
                $customerName = $customer->getName();
                if (is_numeric($minimum_time)) {
                        $processTime = $minimum_time;
                }
                $sellerTimeSlotData = $this->getTimeSlotData($sellerId, $allowedDays, $processTime, $maxDays);
                $sellerTimeSlotData['name'] = $customerName;
                $sellerTimeSlotData['message'] = $message;

                $config['seller'][$sellerId] = $sellerTimeSlotData;
            }
        }

        if (in_array(0, $sellerIds) || empty($config['seller'])) {
            $sellerId = 0;
            $customerName = __('Admin');
            $sellerTimeSlotData = $this->getTimeSlotData($sellerId, $allowedDays, $processTime, $maxDays);
            $sellerTimeSlotData['name'] = $customerName;
            $sellerTimeSlotData['message'] = $message;

            $config['seller'][$sellerId] = $sellerTimeSlotData;
        }

        return $config;
    }

    /**
     * Retrieve current store id
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getStoreId();
    }

    /**
     * Retrieve seller's ids for quote's items
     *
     * @return array
     */
    private function getSellerIds()
    {
        $sellerIds = [];
        if ($this->checkoutSessionFactory->create()->getQuote()->getId()) {
            $quote = $this->quoteRepository->get($this->checkoutSessionFactory->create()->getQuote()->getId());
            foreach ($quote->getAllItems() as $item) {
                $productID = $item->getProduct()->getId();
                if ($item->getProduct()->isVirtual() || $item->getParentItem() || $this->isPreOrder($productID)) {
                    continue;
                }
                $mpAssignProductId = $this->_helper->getAssignProduct($item);
                $sellerIds[] = $this->_helper->getSellerId($mpAssignProductId, $item->getProductId());
            }
        }
        return $sellerIds;
    }
    
    /**
     * Check Pre Order
     *
     * @param int $productID
     * @return bool
     */
    private function isPreOrder($productId)
    {
        if ($this->moduleManager->isEnabled('Webkul_Preorder')) {
            return $this->objectManager
                ->create(Data::class)
                ->isPreOrder($productId);
        }
        return false;
    }

    /**
     * check whether slot is available or not
     *
     * @param object $slot
     * @param int $sellerId
     * @param int $date
     *
     * @return bool
     */
    private function checkAvailabilty($slot, $sellerId, $date)
    {
        $date = $this->_date->gmtDate(date('Y-m-d', $date));
        $collection = $this->_timeSlotOrderFactory->create()
            ->getCollection()
            ->addFieldToFilter('seller_id', ['eq' => $sellerId])
            ->addFieldToFilter('slot_id', ['eq' => $slot->getEntityId()])
            ->addFieldToFilter('selected_date', ['eq' => $date]);
        if ($collection->getSize() >= $slot->getOrderCount()) {
            return false;
        }
        return true;
    }

    /**
     * return timeSlotCollection for Seller Id
     *
     * @param int $sellerId
     *
     * @return object
     */
    public function getTimeSlotCollection($sellerId)
    {
        $collection = $this->_timeSlotCollection->create()
                ->addFieldToFilter('seller_id', ['eq' => $sellerId]);

        if (!$collection->getSize()) {
            $collection = $this->_timeSlotCollection->create()
                ->addFieldToFilter('seller_id', ['eq' => 0]);
        }

        return $collection;
    }
    
    /**
     * return timeSlotCollection for Seller Id
     *
     * @param int $sellerId
     * @param array $allowedDays
     * @param int $processTime
     * @param int $maxDays
     *
     * @return array
     */
    public function getTimeSlotData($sellerId, $allowedDays, $processTime, $maxDays)
    {
        $startDate = "0";
        $dateWiseSlots = $timeSlotData = [];
        $collection = $this->getTimeSlotCollection($sellerId);

        if ($collection->getSize()) {
            foreach ($collection as $slot) {
                if (!in_array($slot->getDeliveryDay(), $allowedDays)) {
                    continue;
                }
                $startTime = $this->_date->gmtDate('h:i A', $slot->getStartTime());
                $endTime = $this->_date->gmtDate('h:i A', $slot->getEndTime());
                $startDate = strtotime("+".$processTime." day", strtotime(date('Y-m-d')));
                
                for ($i=0; $i < $maxDays; $i++) {
                    $d = strtotime("+".$i." day", $startDate);
                    if (ucfirst($slot->getDeliveryDay()) == date('l', $d)) {
                        $isAvailable = $this->checkAvailabilty($slot, $sellerId, $d);
                        $dateWiseSlots[date('Y-m-d', $d)][] = [
                            'slot'=>$startTime.'-'.$endTime,
                            'is_available'=>$isAvailable,
                            'slot_id'   => $slot->getEntityId()
                        ];
                    }
                }
            }
        }

        $startDate = date("Y-m-d", $startDate);
        $timeSlotData['id'] = $sellerId;
        $timeSlotData['slots'] = $dateWiseSlots;
        $timeSlotData['seller_start_date'] = $startDate;
        
        return $timeSlotData;
    }
}
