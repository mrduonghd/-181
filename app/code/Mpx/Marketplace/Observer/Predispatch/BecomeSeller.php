<?php

namespace Mpx\Marketplace\Observer\Predispatch;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;
use Webkul\Marketplace\Helper\Data as MpHelper;

/**
 * Observer run before action becomeSeller
 */
class BecomeSeller implements ObserverInterface
{
    /**
     * Action controller becomeseller
     */
    public const WEBKUL_BECOMESELLER_FULL_ACTION = "marketplace_account_becomeseller";

    /**
     * Enable setting 403 page
     */
    public const MPX_403_PAGE_ENABLE_CONFIG = "mpx_web/default/enable";

    /**
     * @var MpHelper
     */
    protected $wkMpHelper;

    /**
     * @var ForwardFactory
     */
    protected $forwardFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param MpHelper $wkMpHelper
     * @param ForwardFactory $forwardFactory
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        MpHelper $wkMpHelper,
        ForwardFactory $forwardFactory,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->wkMpHelper = $wkMpHelper;
        $this->forwardFactory = $forwardFactory;
        $this->customerSession = $customerSession;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Main execute function
     *
     * @param Observer $observer
     * @return \Magento\Framework\Controller\Result\Forward|void
     */
    public function execute(Observer $observer)
    {
        /* @var RequestInterface $request */
        $request = $observer->getRequest();
        $fullActionName = $request->getFullActionName();
        $enableModule = $this->scopeConfig
            ->getValue(self::MPX_403_PAGE_ENABLE_CONFIG, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        if (!$enableModule) {
            return;
        }
        if ($fullActionName == self::WEBKUL_BECOMESELLER_FULL_ACTION &&
            !$this->wkMpHelper->isSeller() && $this->customerSession->isLoggedIn()) {
            $resultForward = $this->forwardFactory->create();
            $resultForward->setModule('mpx');
            $resultForward->setController('seller');
            $resultForward->forward('forbidden');
            return $resultForward;
        }
    }
}
