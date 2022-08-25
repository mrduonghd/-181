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
namespace Webkul\MpTimeDelivery\Model\ResourceModel\TimeSlotOrder;

use \Webkul\MpTimeDelivery\Model\ResourceModel\AbstractCollection;
use Webkul\MpTimeDelivery\Model\TimeSlotOrder;
use Webkul\MpTimeDelivery\Model\ResourceModel\TimeSlotOrder as TimeSlot;

/**
 * Webkul MpTimeDelivery ResourceModel Seller collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            TimeSlotOrder::class,
            TimeSlot::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
        $this->_map['fields']['seller_id'] = 'main_table.seller_id';
        $this->_map['fields']['slot_id'] = 'main_table.slot_id';
        $this->_map['fields']['selected_date'] = 'main_table.selected_date';
    }
    
    /**
     * Add filter by store
     *
     * @param  int|array|\Magento\Store\Model\Store $store
     * @param  bool                                 $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }
    
    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    public function getDeliveryOrderCollection()
    {
        $customerGridFlat = $this->getTable('customer_grid_flat');
        $timeSlotConfig = $this->getTable('marketplace_timeslot_config');
        $salesOrder = $this->getTable('sales_order');
        $this->getSelect()->join(
            $customerGridFlat.' as customer',
            'main_table.seller_id = customer.entity_id',
            ['name' => 'name']
        );
        $this->addFilterToMap('name', 'customer.name');
        $this->getSelect()->join(
            $timeSlotConfig.' as sc',
            'main_table.slot_id = sc.entity_id',
            ['start_time' => 'start_time', 'end_time'=>'end_time']
        );
        $this->addFilterToMap('start_time', 'sc.start_time');
        $this->addFilterToMap('end_time', 'sc.end_time');

        $this->getSelect()->join(
            $salesOrder.' as sales',
            'main_table.order_id = sales.entity_id',
            [
                'increment_id' => 'increment_id',
                'created_at' => 'created_at'
            ]
        );
        $this->addFilterToMap('increment_id', 'sales.increment_id');
        
        return $this;
    }
}
