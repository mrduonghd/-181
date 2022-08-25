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

interface SellerManagementInterface
{
    /**
     * depricated
     *
     * get seller details.
     *
     * @api
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getSellerList();

    /**
     * Interface get seller details.
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
     * Interface for managing customers orders.
     *
     * @api
     *
     * @param int $id seller id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getSellerOrders($id);

    /**
     * Interface for getting seller sales details.
     *
     * @api
     *
     * @param int $id seller id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getSellerSalesDetails($id);

    /**
     * Interface for creating seller order invoice.
     *
     * @api
     *
     * @param int $id      seller id
     * @param int $orderId order id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function createInvoice($id, $orderId);

    /**
     * Interface to vew invoice.
     *
     * @api
     *
     * @param int $id        seller id
     * @param int $orderId
     * @param int $invoiceId
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function viewInvoice($id, $orderId, $invoiceId);

    /**
     * Interface for cancel order.
     *
     * @api
     *
     * @param int $id      seller id
     * @param int $orderId
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function cancelOrder($id, $orderId);

    /**
     * Interface for creating credit memo.
     *
     * @api
     *
     * @param int $id        seller id
     * @param int $orderId
     * @param int $invoiceId
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function createCreditmemo($id, $invoiceId, $orderId);

    /**
     * Interface to view credit memp.
     *
     * @api
     *
     * @param int $id           seller id
     * @param int $orderId
     * @param int $creditmemoId
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function viewCreditmemo($id, $orderId, $creditmemoId);

    /**
     * Interface for generating shipment.
     *
     * @api
     *
     * @param int $id      seller id
     * @param int $orderId
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function ship($id, $orderId);

    /**
     * Interface to view shipment.
     *
     * @api
     *
     * @param int $id         seller id
     * @param int $orderId
     * @param int $shipmentId
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function viewShipment($id, $orderId, $shipmentId);

    /**
     * Interface for mail to admin.
     *
     * @api
     *
     * @param int $id seller id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function mailToAdmin($id);

    /**
     * Interface for mail to seller.
     *
     * @api
     *
     * @param int $id seller id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function mailToSeller($id);

    /**
     * become partner .
     *
     * @api
     *
     * @param int $id seller id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function becomePartner($id);

    /**
     * get landing page data.
     *
     * @api
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getLandingPageData();

    /**
     * get seller reviews .
     *
     * @api
     *
     * @param int $id seller id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getSellerReviews($id);

    /**
     * get seller reviews .
     *
     * @api
     *
     * @param int $id seller id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function makeSellerReview($id);

    /**
     * get review .
     *
     * @api
     *
     * @param int $review_id review id
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getReview($review_id);

    /**
     * create seller account
     *
     * @api
     *
     * @return Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function createAccount();
}
