<?php

/**
 * Mpshipping Interface
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpshipping\Api\Data;

interface MpshippingInterface
{
    /**#@+
     * Constants for keys of data array. Identical to the name of the getter in snake case
     */
    const MPSHIPPING_ID = 'mpshipping_id';
    /**#@-*/

    /**
     * Get Mpshipping ID
     *
     * @return int|null
     */
    public function getMpshippingId();
    /**
     * Set Mpshipping ID
     *
     * @param int $id
     * @return \Webkul\Mpshipping\Api\Data\MpshippingInterface
     */
    public function setMpshippingId($id);
}
