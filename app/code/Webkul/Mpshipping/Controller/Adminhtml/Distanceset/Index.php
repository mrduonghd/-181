<?php

/**
 * Mpshipping Admin Distanceset Index Controller.
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpshipping\Controller\Adminhtml\Distanceset;

use Webkul\Mpshipping\Controller\Adminhtml\Distanceset as DistancesetController;
use Magento\Framework\Controller\ResultFactory;

class Index extends DistancesetController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_Mpshipping::distanceset');
        $resultPage->getConfig()->getTitle()->prepend(__('Shipping By Distance'));
        $resultPage->addBreadcrumb(
            __('Shipping By Distance'),
            __('Shipping By Distance')
        );
        return $resultPage;
    }
}
