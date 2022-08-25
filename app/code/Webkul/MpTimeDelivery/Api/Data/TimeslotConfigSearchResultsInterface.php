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
namespace Webkul\MpTimeDelivery\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for time slots search results.
 *
 * @api
 */
interface TimeslotConfigSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get Time Slot Config list.
     *
     * @return \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface[]
     */
    public function getItems();

    /**
     * Set Time Slot Config list.
     *
     * @param  \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface[] $items
     * @return \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigSearchResultsInterface
     */
    public function setItems(array $items);
}
