<?php
/**
 * Mpshipping Admin Shippingset Controller
 *
 * @category    Webkul
 * @package     Webkul_Mpshipping
 * @author      Webkul Software Private Limited
 *
 */
namespace Webkul\Mpshipping\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class Shippingset extends \Magento\Backend\App\Action
{
    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Mpshipping::mpshippingset');
    }
}
