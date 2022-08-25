<?php

/**
 * Mpshipping Admin Shipping Index Controller.
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpshipping\Controller\Adminhtml\Shipping;

use Webkul\Mpshipping\Controller\Adminhtml\Shipping as ShippingController;
use Magento\Framework\Controller\ResultFactory;

class Index extends ShippingController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_Mpshipping::mpshipping');
        $resultPage->getConfig()->getTitle()->prepend(__('Marketplace Table Rate Shipping Manager'));
        $resultPage->addBreadcrumb(
            __('Marketplace Table Rate Shipping Manager'),
            __('Marketplace Table Rate Shipping Manager')
        );
        $resultPage->addContent(
            $resultPage
            ->getLayout()
            ->createBlock(
                \Webkul\Mpshipping\Block\Adminhtml\Shipping\Edit::class
            )
        );
        $resultPage->addLeft(
            $resultPage
            ->getLayout()
            ->createBlock(
                \Webkul\Mpshipping\Block\Adminhtml\Shipping\Edit\Tabs::class
            )
        );
        return $resultPage;
    }
}
