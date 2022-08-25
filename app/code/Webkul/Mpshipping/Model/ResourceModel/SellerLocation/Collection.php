<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Mpshipping
 * @author Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Mpshipping\Model\ResourceModel\SellerLocation;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\Mpshipping\Model\SellerLocation::class,
            \Webkul\Mpshipping\Model\ResourceModel\SellerLocation::class
        );
    }
}
