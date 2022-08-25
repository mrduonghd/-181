<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpTimeDelivery
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpTimeDelivery\Controller\Seller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\MpTimeDelivery\Helper\Data;
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;
use Magento\Customer\Model\SessionFactory;
use Magento\Customer\Model\UrlFactory;
use Magento\Framework\App\RequestInterface;

class Orders extends Action
{
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;
    
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    
    /**
     * @var \Webkul\MpTimeDelivery\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @var \Magento\Customer\Model\UrlFactory
     */
    protected $urlFactory;
    
    /**
     * @param Context                                   $context
     * @param PageFactory                               $resultPageFactory
     * @param \Webkul\MpTimeDelivery\Helper\Data        $helper
     * @param \Webkul\Marketplace\Helper\Data           $marketplaceHelper
     * @param \Magento\Customer\Model\SessionFactory    $customerSessionFactory
     * @param \Magento\Customer\Model\UrlFactory        $urlFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Data $helper,
        MarketplaceHelper $marketplaceHelper,
        SessionFactory $customerSessionFactory,
        UrlFactory $urlFactory
    ) {
        $this->customerSessionFactory = $customerSessionFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->helper = $helper;
        $this->urlFactory = $urlFactory;
        parent::__construct($context);
    }

    /**
     * Check customer authentication
     *
     * @param  RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->urlFactory->create()->getLoginUrl();

        if (!$this->customerSessionFactory->create()->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Seller Time Delivery Orders Page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        if (!$this->helper->getConfigData('active')) {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/dashboard',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
        if ($this->marketplaceHelper->getIsSeparatePanel()) {
            $resultPage->addHandle('timedelivery_seller_layout2_orders');
        }
        $resultPage->getConfig()->getTitle()->set(__('Time Delivery Orders'));
        return $resultPage;
    }
}
