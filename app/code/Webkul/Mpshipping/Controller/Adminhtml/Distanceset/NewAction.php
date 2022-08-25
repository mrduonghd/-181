<?php
/**
 * Webkul Mpshipping Distanceset New Action Controller
 *
 * @category    Webkul
 * @package     Webkul_Mpshipping
 * @author      Webkul Software Private Limited
 *
 */
namespace Webkul\Mpshipping\Controller\Adminhtml\Distanceset;

class NewAction extends \Webkul\Mpshipping\Controller\Adminhtml\Distanceset
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
