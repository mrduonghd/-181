<?php
/**
 * Distanceset Controller
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpshipping\Controller\Distanceset;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Webkul\Mpshipping\Model\MpshippingDistFactory;
use Magento\Customer\Model\Url;
use Magento\Framework\App\RequestInterface;

class Delete extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var Webkul\Mpshipping\Model\MpshippingDistFactory
     */
    protected $mpshippingDist;
    /**
     * @var Magento\Customer\Model\Url
     */
    protected $_customerUrl;

    /**
     * @param Context           $context
     * @param Session           $customerSession
     * @param MpshippingDistFactory $mpshippingDist
     * @param Url               $customerUrl
     */

    public function __construct(
        Context $context,
        Session $customerSession,
        MpshippingDistFactory $mpshippingDist,
        Url $customerUrl
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->mpshippingDist = $mpshippingDist;
        $this->_customerUrl = $customerUrl;
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
     * Delet Shipping rule
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $fields = $this->getRequest()->getParams();
            if (!empty($fields)) {
                $shippingModel = $this->mpshippingDist->create()
                    ->load($fields['id']);
                if (!empty($shippingModel)) {
                    $shippingModel->delete();
                    $this->messageManager->addSuccess(__('Shipping Rule is successfully Deleted!'));
                    return $resultRedirect->setPath('mpshipping/distanceset/view');
                } else {
                    $this->messageManager->addError(__('No record Found!'));
                    return $resultRedirect->setPath('mpshipping/distanceset/view');
                }
            } else {
                $this->messageManager->addSuccess(__('Please try again!'));
                return $resultRedirect->setPath('mpshipping/distanceset/view');
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            return $resultRedirect->setPath('mpshipping/distanceset/view');
        }
    }
}
