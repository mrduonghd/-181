<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpshipping\Api\Data;

interface SellerLocationInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const ENTITY_ID    = 'entity_id';
    const PARTNER_ID = 'partner_id';
    const LOCATION = 'location';
    const LATITUDE = 'latitude';
    const LONGITUDE = 'longitude';
   
    /**#@-*/

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId();

    /**
     * Set ID
     *
     * @param int $id
     */
    public function setEntityId($id);

    /**
     * Get Partner Id
     *
     * @return int|null
     */
    public function getPartnerId();

    /**
     * Set Partner Id
     *
     * @param int $partnerId
     */
    public function setPartnerId($partnerId);
    /**
     * Get Location
     *
     * @return int|null
     */
    public function getLocation();

    /**
     * Set Location
     *
     * @param int $location
     */
    public function setLocation($location);
    
    /**
     * Get Latitude
     *
     * @return int|null
     */
    public function getLatitude();

    /**
     * Set Latitude
     *
     * @param int $latitude
     */
    public function setLatitude($latitude);

    /**
     * Get Longitude
     *
     * @return int|null
     */
    public function getLongitude();

    /**
     * Set Longitude
     *
     * @param int $longitude
     */
    public function setLongitude($longitude);
}
