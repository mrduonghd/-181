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
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\RequestInterface;
use Webkul\Mpshipping\Model\MpshippingmethodFactory;
use Webkul\Mpshipping\Model\MpshippingFactory;
use Magento\Customer\Model\Url;

class Edit extends Action
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
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;
    /**
     * @var Webkul\Mpshipping\Model\MpshippingFactory
     */
    protected $_mpshippingModel;
    /**
     * @var Magento\Customer\Model\Url
     */
    protected $_customerUrl;
    /**
     * @var Webkul\Mpshipping\Helper\Data
     */
    protected $_mpshippingHelperData;

    /**
     * @param Context                 $context
     * @param Session                 $customerSession
     * @param MpshippingmethodFactory $shippingmethodFactory
     * @param MpshippingFactory       $mpshippingModel
     * @param Url                     $customerUrl
     */

    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        MpshippingmethodFactory $shippingmethodFactory,
        MpshippingFactory $mpshippingModel,
        \Webkul\Mpshipping\Helper\Data $mpshippingHelperData,
        Url $customerUrl
    ) {
        parent::__construct($context);
        $this->_session = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_mpshippingMethod = $shippingmethodFactory;
        $this->_mpshippingModel = $mpshippingModel;
        $this->_mpshippingHelperData = $mpshippingHelperData;
        $this->_customerUrl = $customerUrl;
    }

    /**
     * Check Customer Authentication.
     *
     * @param object RequestInterface $request
     *
     * @return object \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $model = $this->_customerUrl;
        $url = $model->getLoginUrl();
        if (!$this->_session->authenticate($url)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Edit shipping rate.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                            return $this->resultRedirectFactory->create()->setPath(
                                '*/*/view',
                                ['_secure' => $this->getRequest()->isSecure()]
                            );
                }
                $params = $this->getRequest()->getParams();
                $params['shipping_method'] = htmlentities($params['shipping_method']);
                $params['dest_region_id'] = htmlentities($params['dest_region_id']);
                $params['dest_zip'] = htmlentities($params['dest_zip']);
                $params['dest_zip_to'] = htmlentities($params['dest_zip_to']);
                $params['zipcode'] = htmlentities($params['zipcode']);
                $partnerid = $this->_mpshippingHelperData->getPartnerId();
                if (!empty($params)) {
                    $shippingData = $this->_mpshippingModel
                        ->create()
                        ->load($params['id']);
                    if (!empty($shippingData)) {
                        $shippingMethodId = $this->getShippingNameById($params['shipping_method']);
                        if ($shippingMethodId==0) {
                            $mpshippingMethod = $this->_mpshippingMethod->create();
                            $mpshippingMethod->setMethodName($params['shipping_method']);
                            $savedMethod = $mpshippingMethod->save();
                            $shippingMethodId = $savedMethod->getEntityId();
                        }
                        $params['shipping_method_id'] = $shippingMethodId;
                        $shippingCollection = $this->_mpshippingModel->create()
                            ->getCollection()
                            ->addFieldToFilter('partner_id', $partnerid)
                            ->addFieldToFilter('dest_country_id', $params['dest_country_id'])
                            ->addFieldToFilter('dest_region_id', $params['dest_region_id'])
                            ->addFieldToFilter('dest_zip', ['gteq'=>$params['dest_zip']])
                            ->addFieldToFilter('dest_zip_to', ['lteq' =>$params['dest_zip_to']])
                            ->addFieldToFilter('weight_from', ['lteq' =>$params['weight_from']])
                            ->addFieldToFilter('weight_to', ['gteq' =>$params['weight_to']])
                            ->addFieldToFilter('shipping_method_id', $shippingMethodId)
                            ->addFieldToFilter('mpshipping_id', ['neq' => $params['id']]);
                        if ($params['is_range'] == 'no') {
                            $shippingCollection->addFieldToFilter('is_range', ['eq'=>$params['is_range']]);
                            $shippingCollection->addFieldToFilter('zipcode', ['eq'=>$params['zipcode']]);
                        }
                        if ($shippingCollection->getSize()) {
                            $this->messageManager->addError(__('Shipping rule already exist for the given range.'));
                            return $this->resultRedirectFactory->create()->setPath('mpshipping/shipping/view');
                        }
                        $shippingData->addData($params);
                        $shippingData->setMpShippingId($params['id'])->save();
                        $this->messageManager->addSuccess(__('Your shipping detail has been successfully updated.'));
                        return $this->resultRedirectFactory->create()->setPath('mpshipping/shipping/view');
                    } else {
                        $this->messageManager->addError(__('No record Found!'));
                        return $this->resultRedirectFactory->create()->setPath('mpshipping/shipping/view');
                    }
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath('mpshipping/shipping/view');
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath('mpshipping/shipping/view');
        }
    }

    public function getShippingNameById($shippingMethodName)
    {
        $entityId = 0;
        $shippingMethodModel = $this->_mpshippingMethod->create()
            ->getCollection()
            ->addFieldToFilter('method_name', $shippingMethodName);
        foreach ($shippingMethodModel as $shippingMethod) {
            $entityId = $shippingMethod->getEntityId();
        }
        return $entityId;
    }
}
