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
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Webkul\Mpshipping\Model\MpshippingmethodFactory;
use Magento\Customer\Model\Url;
use Webkul\Mpshipping\Model\MpshippingFactory;

class Add extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

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
     * @param Context          $context
     * @param Session          $customerSession
     * @param FormKeyValidator $formKeyValidator
     * @param PageFactory      $resultPageFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        PageFactory $resultPageFactory,
        MpshippingmethodFactory $shippingmethodFactory,
        Url $customerUrl,
        \Webkul\Mpshipping\Helper\Data $mpshippingHelperData,
        MpshippingFactory $mpshippingModel
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
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
        $loginUrl = $this->_customerUrl->getLoginUrl();
        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Default customer account page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $partnerid = $this->_mpshippingHelperData->getPartnerId();
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
                $shippingMethodId = $this->getShippingNameById($params['shipping_method']);
                if ($shippingMethodId==0) {
                    $mpshippingMethod = $this->_mpshippingMethod->create();
                    $mpshippingMethod->setMethodName($params['shipping_method']);
                    $savedMethod = $mpshippingMethod->save();
                    $shippingMethodId = $savedMethod->getEntityId();
                }
                $temp = [
                    'dest_country_id' => $params['dest_country_id'],
                    'dest_region_id' => htmlentities($params['dest_region_id']),
                    'dest_zip' => htmlentities($params['dest_zip']),
                    'dest_zip_to' => htmlentities($params['dest_zip_to']),
                    'price' => $params['price'],
                    'weight_from' => $params['weight_from'],
                    'weight_to' => $params['weight_to'],
                    'shipping_method_id' => $shippingMethodId,
                    'partner_id' => $partnerid,
                    'is_range' => $params['is_range'],
                    'zipcode'  => htmlentities($params['zipcode']),
                ];
                $shippingCollection = $this->_mpshippingModel->create()
                    ->getCollection()
                    ->addFieldToFilter('partner_id', $partnerid)
                    ->addFieldToFilter('dest_country_id', $params['dest_country_id'])
                    ->addFieldToFilter('dest_region_id', $params['dest_region_id'])
                    ->addFieldToFilter('dest_zip', ['gteq'=>$params['dest_zip']])
                    ->addFieldToFilter('dest_zip_to', ['lteq' =>$params['dest_zip_to']])
                    ->addFieldToFilter('weight_from', ['lteq' =>$params['weight_from']])
                    ->addFieldToFilter('weight_to', ['gteq' =>$params['weight_to']])
                    ->addFieldToFilter('shipping_method_id', $shippingMethodId);
                if ($temp['is_range'] == 'no') {
                    $shippingCollection->addFieldToFilter('is_range', ['eq'=>$temp['is_range']]);
                    $shippingCollection->addFieldToFilter('zipcode', ['eq'=>$params['zipcode']]);
                }
                if ($shippingCollection->getsize() > 0) {
                    $this->messageManager->addError(__('Shipping rule already exist for the given range.'));
                } else {
                    $shippingModel = $this->_mpshippingModel->create();
                    $shippingModel->setData($temp);
                    $shippingModel->save();
                    $this->messageManager->addSuccess(__('Your shipping detail has been successfully saved'));
                }
                return $this->resultRedirectFactory->create()->setPath('mpshipping/shipping/view');
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
