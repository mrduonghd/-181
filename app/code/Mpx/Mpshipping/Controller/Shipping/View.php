<?php
/**
 * Mpx Software
 *
 * @category  Mpx
 * @package   Mpx_Mpshipping
 * @author    Mpx
 */

namespace Mpx\Mpshipping\Controller\Shipping;

class View extends \Webkul\Mpshipping\Controller\Shipping\View
{

    /**
     * Shipping rate view page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $isPartner = $this->_marketplaceHelperData->isSeller();
        if ($isPartner == 1) {
            $resultPage = $this->_resultPageFactory->create();
            if ($this->_marketplaceHelperData->getIsSeparatePanel()) {
                $resultPage->addHandle('mpshipping_layout2_shipping_view');
            }
            $resultPage->getConfig()->getTitle()->set(__('Table Rate Shipping by Region'));
            return $resultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
