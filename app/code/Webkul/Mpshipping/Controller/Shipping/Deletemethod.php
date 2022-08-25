<?php
/**
 * Mpshipping Controller
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpshipping\Controller\Shipping;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Webkul\Mpshipping\Model\MpshippingmethodFactory;
use Magento\Customer\Model\Url;
use Webkul\Mpshipping\Model\MpshippingFactory;

class Deletemethod extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var Webkul\Mpshipping\Model\MpshippingmethodFactory
     */
    protected $_mpshippingMethod;
    /**
     * @var Magento\Customer\Model\Url
     */
    protected $_customerUrl;
    /**
     * @var Webkul\Mpshipping\Model\MpshippingFactory
     */
    protected $_mpshippingModel;
    /**
     * @var Webkul\Mpshipping\Helper\Data
     */
    protected $_mpshippingHelperData;

    /**
     * @param Context                 $context
     * @param Session                 $customerSession
     * @param MpshippingmethodFactory $shippingmethodFactory
     * @param Url                     $customerUrl
     * @param MpshippingFactory       $mpshippingModel
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        MpshippingmethodFactory $shippingmethodFactory,
        Url $customerUrl,
        \Webkul\Mpshipping\Helper\Data $mpshippingHelperData,
        MpshippingFactory $mpshippingModel
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->_mpshippingMethod = $shippingmethodFactory;
        $this->_customerUrl = $customerUrl;
        $this->_mpshippingHelperData = $mpshippingHelperData;
        $this->_mpshippingModel = $mpshippingModel;
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
        $urlModel = $this->_customerUrl;
        $loginUrl = $urlModel->getLoginUrl();
        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Default Shipping Method.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $partnerId = $this->_mpshippingHelperData->getPartnerId();
            $fields = $this->getRequest()->getParams();
            if (!empty($fields)) {
                $shipMethodModel = $this->_mpshippingMethod->create()->load($fields['id']);
                if (!empty($shipMethodModel)) {
                    $shippingCollection = $this->_mpshippingModel
                        ->create()
                        ->getCollection()
                        ->addFieldToFilter('shipping_method_id', $fields['id'])
                        ->addFieldToFilter('partner_id', $partnerId);
                    foreach ($shippingCollection as $shipping) {
                        $shippingModel = $this->_mpshippingModel
                            ->create()
                            ->load($shipping->getMpshippingId());
                        if (!empty($shippingModel)) {
                            $shippingModel->delete();
                        }
                    }
                    $this->messageManager->addSuccess(__('Shipping Method is successfully Deleted!'));
                    return $resultRedirect->setPath('mpshipping/shipping/view');
                } else {
                    $this->messageManager->addError(__('No record Found!'));
                    return $resultRedirect->setPath('mpshipping/shipping/view');
                }
            } else {
                $this->messageManager->addSuccess(__('Please try again!'));
                return $resultRedirect->setPath('mpshipping/shipping/view');
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $resultRedirect->setPath('mpshipping/shipping/view');
        }
    }
}
