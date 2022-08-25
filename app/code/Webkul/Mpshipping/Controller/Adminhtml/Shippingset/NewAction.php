<?php
/**
 * Webkul Mpshipping Shippingset New Action Controller
 *
 * @category    Webkul
 * @package     Webkul_Mpshipping
 * @author      Webkul Software Private Limited
 *
 */
namespace Webkul\Mpshipping\Controller\Adminhtml\Shippingset;

class NewAction extends \Webkul\Mpshipping\Controller\Adminhtml\Shippingset
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization
                    ->isAllowed('Webkul_Mpshipping::mpshippingset');
    }
}
