<?php
/**
 * Webkul Mpshipping Shippingset Edit Controller
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpshipping\Controller\Adminhtml\Shipping;

use Magento\Framework\Controller\ResultFactory;

use Magento\Framework\Locale\Resolver;

class Edit extends \Webkul\Mpshipping\Controller\Adminhtml\Shippingset
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Webkul\Mpshipping\Model\MpshippingFactory
     */
    private $mpshippingFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry         $coreRegistry
     * @param CollectionFactory                   $mpshippingFactory
     * @param RoleFac                             $salesOrderCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Webkul\Mpshipping\Model\MpshippingFactory $mpshippingFactory
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->mpshippingFactory = $mpshippingFactory;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $id=(int)$this->getRequest()->getParam('id');
        $shippingModel=$this->mpshippingFactory->create();
        if ($id) {
            $shippingModel->load($id);
            if (!$shippingModel->getMpshippingId()) {
                $this->messageManager->addError(__('This Shipping rule is no longer exists.'));
                $this->_redirect('mpshipping/*/');
                return;
            }
        }
        $this->coreRegistry->register('mpshippingrule_shipping', $shippingModel);
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_Mpshipping::mpshipping');
        $resultPage->getConfig()->getTitle()->prepend(__('Shipping Rule'));
        $resultPage->addContent(
            $resultPage
            ->getLayout()
            ->createBlock(
                \Webkul\Mpshipping\Block\Adminhtml\ShippingRule\Edit::class
            )
        );
        $resultPage->addLeft(
            $resultPage
            ->getLayout()
            ->createBlock(
                \Webkul\Mpshipping\Block\Adminhtml\ShippingRule\Edit\Tabs::class
            )
        );
          return $resultPage;
    }

    /**
     * check permission
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Mpshipping::mpshipping');
    }
}
