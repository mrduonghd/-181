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
namespace Webkul\MpTimeDelivery\Model\Seller;

use Magento\Customer\Model\SessionFactory;
use Webkul\MpTimeDelivery\Model\ResourceModel\TimeSlotOrder\CollectionFactory;

class OrderConfigProviders
{
    /**
     * @param CustomerSessionFactory $customerSessionFactory
     * @param CollectionFactory      $timeSlotCollection
     */
    public function __construct(
        SessionFactory $customerSessionFactory,
        CollectionFactory $timeSlotCollection
    ) {
        $this->customerSessionFactory = $customerSessionFactory;
        $this->timeSlotCollection = $timeSlotCollection;
    }

    /**
     * Get Order Collection for Seller Time Slots
     *
     * @return object $collection
     */
    public function getCollection()
    {
        $collection = $this->timeSlotCollection->create()
            ->getDeliveryOrderCollection()
            ->addFieldToFilter('seller_id', $this->_getCustomer()->getId())
            ->setOrder('selected_date', 'DESC');
        
        return $collection;
    }

    /**
     * return current customer session.
     *
     * @return \Magento\Customer\Model\Session
     */
    public function _getCustomer()
    {
        return $this->customerSessionFactory->create()->getCustomer();
    }
}
