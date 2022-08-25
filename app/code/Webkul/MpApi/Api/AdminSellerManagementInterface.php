<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpApi
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpApi\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface AdminSellerManagementInterface
{
    /**
     * get seller details.
     *
     * @api
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return Webkul\MpApi\Api\Data\AdminSellerResultsInterface
     */
    public function getSellerList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
