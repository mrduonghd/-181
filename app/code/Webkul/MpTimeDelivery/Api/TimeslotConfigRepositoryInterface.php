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
namespace Webkul\MpTimeDelivery\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * time slots block CRUD interface.
 *
 * @api
 */
interface TimeslotConfigRepositoryInterface
{
    /**
     * Save TimeSlot Configuration.
     *
     * @param  \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface $items
     * @return \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\TimeslotConfigInterface $items);

    /**
     * Retrieve TimeSlot Configuration.
     *
     * @param  int $id
     * @return \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve TimeSlot Configuration matching the specified criteria.
     *
     * @param  \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete TimeSlot Configuration.
     *
     * @param  \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface $item
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\TimeslotConfigInterface $item);

    /**
     * Delete TimeSlot Configuration by ID.
     *
     * @param  int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}
