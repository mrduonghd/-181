<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpshipping\ViewModel;

class LocationModel implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * Helper
     *
     * @var \Webkul\Marketplace\Helper\Data
     */
    public $helper;

    /**
     * Mpshipping Helper
     *
     * @var \Webkul\Mpshipping\Helper\Data
     */
    public $mpHelper;

    /**
     * @param \Webkul\Mpshipping\Helper\Data $mpHelper
     * @param \Webkul\Marketplace\Helper\Data $helper
     */
    public function __construct(
        \Webkul\Mpshipping\Helper\Data $mpHelper,
        \Webkul\Marketplace\Helper\Data $helper
    ) {
        $this->mpHelper = $mpHelper;
        $this->helper = $helper;
    }

    public function getMpHelper()
    {
        return $this->helper;
    }

    public function getHelper()
    {
        return $this->mpHelper;
    }
}
