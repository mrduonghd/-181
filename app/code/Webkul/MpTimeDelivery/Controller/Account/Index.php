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
namespace Webkul\MpTimeDelivery\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

use Magento\Framework\App\RequestInterface;

class Index extends Action
{
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $_customerSessionFactory;
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @var \Webkul\MpTimeDelivery\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Customer\Model\UrlFactory
     */
    protected $urlFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Webkul\MpTimeDelivery\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param \Magento\Customer\Model\SessionFactory $customerSessionFactory
     * @param \Magento\Customer\Model\UrlFactory $urlFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\MpTimeDelivery\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        \Magento\Customer\Model\UrlFactory $urlFactory
    ) {
        $this->_customerSessionFactory = $customerSessionFactory;
        $this->_urlFactory = $urlFactory;
        $this->resultPageFactory = $resultPageFactory;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->helper = $helper;
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
        $loginUrl = $this->_urlFactory->create()->getLoginUrl();

        if (!$this->_customerSessionFactory->create()->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Default Seller Time Delivery Configuration Page.
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
            $resultPage->addHandle('timedelivery_account_layout2_index');
        }
        $resultPage->getConfig()->getTitle()->set(__('Time Delivery Configuration'));
        return $resultPage;
    }
}
