<?php
/**
 * Shippingset Controller.
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpshipping\Controller\Shippingset;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Webkul\Mpshipping\Model\MpshippingsetFactory;
use Magento\Customer\Model\Url;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

/**
 * Webkul Mpshipping Shippingset Massdelete controller.
 */
class Massdelete extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var MpshippingsetFactory
     */
    protected $_mpshippingsetFactory;
    /**
     * @var Magento\Customer\Model\Url
     */
    protected $_customerUrl;
    /**
     * @var Webkul\Mpshipping\Helper\Data
     */
    protected $_mpshippingHelperData;
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;
    /**
     * @param Context           $context
     * @param Session           $customerSession
     * @param Registry          $coreRegistry
     * @param MpshippingsetFactory $mpshippingsetFactory
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        Registry $coreRegistry,
        MpshippingsetFactory $mpshippingsetFactory,
        \Webkul\Mpshipping\Helper\Data $mpshippingHelperData,
        FormKeyValidator $formKeyValidator,
        Url $customerUrl
    ) {
        $this->_customerSession = $customerSession;
        $this->_coreRegistry = $coreRegistry;
        $this->_mpshippingsetFactory = $mpshippingsetFactory;
        $this->_mpshippingHelperData = $mpshippingHelperData;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_customerUrl = $customerUrl;
        parent::__construct(
            $context
        );
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
        $model = $this->_customerUrl;
        $url = $model->getLoginUrl();
        if (!$this->_customerSession->authenticate($url)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Mass delete seller Shipping set action.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $wholedata = $this->getRequest()->getParams();
            if ($this->getRequest()->isPost()) {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                            return $this->resultRedirectFactory->create()->setPath(
                                '*/*/view',
                                ['_secure' => $this->getRequest()->isSecure()]
                            );
                }
                $ids = $this->getRequest()->getParam('shippingset_mass_delete');
                $sellerId = $this->_mpshippingHelperData->getPartnerId();
                $shippingSetCollection = $this->_mpshippingsetFactory->create()
                    ->getCollection()
                    ->addFieldToFilter('partner_id', ['eq'=>$sellerId])
                    ->addFieldToFilter('entity_id', ['in'=>$ids]);
                foreach ($shippingSetCollection as $shippingset) {
                    $shippingset->delete();
                }
                $this->messageManager->addSuccess(
                    __('Shipping sets are successfully deleted from your account.')
                );
            }
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/view',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());

            return $this->resultRedirectFactory->create()->setPath(
                '*/*/view',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
