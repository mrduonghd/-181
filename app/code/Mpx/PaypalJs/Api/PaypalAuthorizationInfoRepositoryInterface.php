<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Magenest_mee231 extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_mee231
 */

namespace Mpx\PaypalJs\Api;

use \Mpx\PaypalJs\Api\Data\PaypalAuthorizationInfoInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface PaypalAuthorizationInfoRepositoryInterface
{
    /**
     * Save Paypal Authorization.
     *
     * @param PaypalAuthorizationInfoInterface $object
     * @return PaypalAuthorizationInfoInterface
     */
    public function save(PaypalAuthorizationInfoInterface $object);

    /**
     * Retrieve Paypal Authorization Info.
     *
     * @param $id
     * @return PaypalAuthorizationInfoInterface
     */
    public function getById($id);

    /**
     * Retrieve Paypal Authorization list.
     *
     * @param SearchCriteriaInterface $criteria
     * @return PaypalAuthorizationInfoInterface
     */
    public function getList(SearchCriteriaInterface $criteria);

    /**
     * Delete Paypal Authorization Info.
     *
     * @param PaypalAuthorizationInfoInterface $object
     * @return bool true on success
     */
    public function delete(PaypalAuthorizationInfoInterface $object);

    /**
     * Delete Paypal Authorization Info by ID.
     *
     * @param $id
     * @return bool true on success
     */
    public function deleteById($id);
}
