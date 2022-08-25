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

interface AdminManagementInterface
{
    /**
     * depricated
     * get seller details.
     *
     * @api
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getSellerList();

    /**
     * Interface for specific seller details.
     *
     * @api
     *
     * @param int $id seller id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getSeller($id);

    /**
     * get seller products.
     *
     * @api
     *
     * @param int $id seller id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getSellerProducts($id);

    /**
     * Interface for seller orders.
     *
     * @api
     *
     * @param int $id seller id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getSellerOrders($id);

    /**
     * Interface for order details.
     *
     * @api
     *
     * @param int $id seller id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getSellerSalesDetails($id);

    /**
     * Interface for paying the seller.
     *
     * @api
     *
     * @param int $id seller id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function payToSeller($id);
    
    /**
     * Interface for assign product(s) to the seller.
     *
     * @api
     *
     * @param int $sellerId seller id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function assignProduct($sellerId);
   
    /**
     * Interface for assign product(s) to the seller.
     *
     * @api
     *
     * @param int $sellerId seller id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function unassignProduct($sellerId);
}
