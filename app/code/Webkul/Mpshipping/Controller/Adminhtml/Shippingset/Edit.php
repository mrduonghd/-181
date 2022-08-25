<?php
/**
 * Webkul Mpshipping Shippingset Edit Controller
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpshipping\Controller\Adminhtml\Shippingset;

use Magento\Framework\Controller\ResultFactory;

use Magento\Framework\Locale\Resolver;

class Edit extends \Webkul\Mpshipping\Controller\Adminhtml\Shippingset
{
    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;

    /**
     * @var \Webkul\Mpshipping\Model\MpshippingsetFactory
     */
    private $mpshippingsetFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry         $coreRegistry
     * @param CollectionFactory                   $mpshippingsetFactory
     * @param RoleFac                             $salesOrderCollectionFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Webkul\Mpshipping\Model\MpshippingsetFactory $mpshippingsetFactory
    ) {
        parent::__construct($context);
        $this->coreRegistry = $coreRegistry;
        $this->mpshippingsetFactory = $mpshippingsetFactory;
    }

    /**
     * @return void
     */
    public function execute()
    {
        $id=(int)$this->getRequest()->getParam('id');
        $shippingsetModel=$this->mpshippingsetFactory->create();
        if ($id) {
            $shippingsetModel->load($id);
            if (!$shippingsetModel->getEntityId()) {
                $this->messageManager->addError(__('This Shiiping Set no longer exists.'));
                $this->_redirect('mpshipping/*/');
                return;
            }
        }

        $this->coreRegistry->register('mpshippingset_shipping', $shippingsetModel);
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_Mpshipping::mpshipping');
        $resultPage->getConfig()->getTitle()->prepend(__('Marketplace Super shipping set'));
        $resultPage->addContent(
            $resultPage
            ->getLayout()
            ->createBlock(
                \Webkul\Mpshipping\Block\Adminhtml\Shippingset\Edit::class
            )
        );
        $resultPage->addLeft(
            $resultPage
            ->getLayout()
            ->createBlock(
                \Webkul\Mpshipping\Block\Adminhtml\Shippingset\Edit\Tabs::class
            )
        );
          return $resultPage;
    }

    /**
     * check permission
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Mpshipping::mpshippingset');
    }
}
