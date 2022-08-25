<?php

namespace Mpx\PaypalJs\Model\ResourceModel\PaypalAuthorization;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Mpx\PaypalJs\Model\PaypalAuthorization;

/**
 * class Collection
 * Get Data DB
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'id';

    /**
     *  Initialize resource collection
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(PaypalAuthorization::class, \Mpx\PaypalJs\Model\ResourceModel\PaypalAuthorization::class);
    }
}
