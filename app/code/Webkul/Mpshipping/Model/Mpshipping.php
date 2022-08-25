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

use Webkul\Mpshipping\Api\Data\MpshippingInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class Mpshipping extends AbstractModel implements MpshippingInterface, IdentityInterface
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
        $this->_init(\Webkul\Mpshipping\Model\ResourceModel\Mpshipping::class);
    }
    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getMpshippingId()];
    }
    /**
     * Get ID
     *
     * @return int|null
     */
    public function getMpshippingId()
    {
        return $this->getData(self::MPSHIPPING_ID);
    }
    public function setMpshippingId($id)
    {
        return $this->setData(self::MPSHIPPING_ID, $id);
    }
}
