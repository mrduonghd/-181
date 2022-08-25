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

namespace Webkul\Mpshipping\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\DataObject\IdentityInterface;
use Webkul\Mpshipping\Api\Data\SellerLocationInterface;

class SellerLocation extends \Magento\Framework\Model\AbstractModel implements SellerLocationInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'mpshipping_sellerlocation';
 
    /**
     * @var string
     */
    protected $_cacheTag = 'mpshipping_sellerlocation';
 
    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'mpshipping_sellerlocation';
 
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Mpshipping\Model\ResourceModel\SellerLocation::class);
    }
    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }
 
    /**
     * Set EntityId.
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }
 
    /**
     * Get PartnerId.
     *
     * @return varchar
     */
    public function getPartnerId()
    {
        return $this->getData(self::PARTNER_ID);
    }
 
    /**
     * Set PartnerId.
     */
    public function setPartnerId($partnerId)
    {
        return $this->setData(self::PARTNER_ID, $partnerId);
    }
 
    /**
     * Get Location.
     *
     * @return varchar
     */
    public function getLocation()
    {
        return $this->getData(self::LOCATION);
    }
 
    /**
     * Set Location.
     */
    public function setLocation($location)
    {
        return $this->setData(self::LOCATION, $location);
    }
 
    /**
     * Get Latitude.
     *
     * @return varchar
     */
    public function getLatitude()
    {
        return $this->getData(self::LATITUDE);
    }
 
    /**
     * Set Latitude.
     */
    public function setLatitude($latitude)
    {
        return $this->setData(self::LATITUDE, $latitude);
    }
 
    /**
     * Get Longitude.
     *
     * @return varchar
     */
    public function getLongitude()
    {
        return $this->getData(self::LONGITUDE);
    }
 
    /**
     * Set Longitude.
     */
    public function setLongitude($longitude)
    {
        return $this->setData(self::LONGITUDE, $longitude);
    }
}
