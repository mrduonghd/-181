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
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;
use Magento\Customer\Model\Url as CustomerUrl;

/**
 * Webkul Location Mpshipping Controller.
 */
class Location extends Action
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
     * @param PageFactory                     $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param MarketplaceHelper               $marketplaceHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        MarketplaceHelper $marketplaceHelper,
        CustomerUrl $customerUrl
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->_customerSession = $customerSession;
        $this->customerUrl = $customerUrl;
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
        $loginUrl = $this->customerUrl->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Seller Dashboard page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->marketplaceHelper;
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            /** @var \Magento\Framework\View\Result\Page $resultPage */
            $resultPage = $this->_resultPageFactory->create();
            if ($helper->getIsSeparatePanel()) {
                $resultPage->addHandle('mpshipping_layout2_seller_location');
            }
            $resultPage->getConfig()->getTitle()->set(__('Set Location'));

            return $resultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );

        }
    }
}
