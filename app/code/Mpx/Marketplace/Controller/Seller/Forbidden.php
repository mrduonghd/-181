<?php

namespace Mpx\Marketplace\Controller\Seller;

use Magento\Cms\Helper\Page;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Forbidden action if non-seller access url become seller
 */
class Forbidden extends Action
{
    /**
     * Path config value defaul page
     */
    public const WEBKUL_BECOMESELLER_CONFIG_DEFAULT_PAGE = 'mpx_web/default/non_seller';

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Page
     */
    protected $pageHelper;

    /**
     * @param Context $context
     * @param ForwardFactory $resultForwardFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param Page $pageHelper
     */
    public function __construct(
        Context $context,
        ForwardFactory $resultForwardFactory,
        ScopeConfigInterface $scopeConfig,
        Page $pageHelper
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->scopeConfig = $scopeConfig;
        $this->pageHelper = $pageHelper;
        parent::__construct($context);
    }

    /**
     * Render CMS 403 Seller forbidden page
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $mpxNoRoute = $this->scopeConfig
            ->getValue(self::WEBKUL_BECOMESELLER_CONFIG_DEFAULT_PAGE, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $resultPage = $this->pageHelper->prepareResultPage($this, $mpxNoRoute);
        if ($resultPage) {
            $resultPage->setStatusHeader(403, '1.1', 'Forbidden');
            $resultPage->setHeader('Status', '403 Forbidden');
            return $resultPage;
        } else {
            $resultForward = $this->resultForwardFactory->create();
            $resultForward->setController('index');
            $resultForward->forward('defaultNoRoute');
            return $resultForward;
        }
    }
}
