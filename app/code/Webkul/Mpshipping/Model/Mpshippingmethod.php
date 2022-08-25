<?php
/**
 * Mpshipping
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpshipping\Model;

use Webkul\Mpshipping\Api\Data\MpshippingMethodInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class Mpshippingmethod extends AbstractModel implements MpshippingMethodInterface, IdentityInterface
{
    /**
     * CMS page cache tag
     */
    const CACHE_TAG = 'webkulshipping';
    /**
     * @var string
     */
    protected $_cacheTag = 'webkulshipping';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'webkulshipping';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Mpshipping\Model\ResourceModel\Mpshippingmethod::class);
    }
    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getEntityId()];
    }
    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }
    public function setEntityId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
