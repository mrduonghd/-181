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
namespace Webkul\Mpshipping\Controller\Seller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Url;
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;

/**
 * Webkul Mpshipping SaveShipping Controller.
 */
class SaveShipping extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var MarketplaceHelper
     */
    protected $marketplaceHelper;

    /**
     * @var CustomerUrl
     */
    protected $customerUrl;

    /**
     * @param Context                         $context
     * @param  \Webkul\Mpshipping\Model\SellerLocationFactory $sellerLocation
     * @param PageFactory                     $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param MarketplaceHelper               $marketplaceHelper
     */
    public function __construct(
        Context $context,
        \Webkul\Mpshipping\Model\SellerLocationFactory $sellerLocation,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        Url $customerUrl,
        MarketplaceHelper $marketplaceHelper
    ) {
        $this->sellerLocation = $sellerLocation;
        $this->_customerSession = $customerSession;
        $this->_customerUrl = $customerUrl;
        $this->_resultPageFactory = $resultPageFactory;
        $this->marketplaceHelper = $marketplaceHelper;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_customerUrl->getLoginUrl();
        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    public function execute()
    {
   
        $wholedata = $this->getRequest()->getParams();
        $helper = $this->marketplaceHelper;
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            $wholedata = $this->getRequest()->getParams();
            $wholedata['partner_id'] = $helper->getCustomerId();
            $sellerLocation =  $this->sellerLocation->create();
            $shippingData = $sellerLocation->getCollection()
                            ->addFieldToFilter('partner_id', $wholedata['partner_id'])
                            ->getFirstItem();
            if ($shippingData->getData()) {
                $sellerLocation->load($shippingData->getId())->addData($wholedata)->save();
            } else {
                $sellerLocation->addData($wholedata)->save();
            }
            $this->messageManager->addSuccess(
                __('Seller location was successfully saved')
            );
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/location',
                ['_secure' => $this->getRequest()->isSecure()]
            );

        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );

        }
    }
}
