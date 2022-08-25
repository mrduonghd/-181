<?php

/**
 * Mpshipping Admin Shippingset Index Controller.
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpshipping\Controller\Adminhtml\Shippingset;

use Webkul\Mpshipping\Controller\Adminhtml\Shippingset as ShippingsetController;
use Magento\Framework\Controller\ResultFactory;

class Index extends ShippingsetController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_Mpshipping::mpshippingset');
        $resultPage->getConfig()->getTitle()->prepend(__('Marketplace Super Shipping Set Manager'));
        $resultPage->addBreadcrumb(
            __('Marketplace Super Shipping Set Manager'),
            __('Marketplace Super Shipping Set Manager')
        );
        return $resultPage;
    }
}
