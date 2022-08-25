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
use Magento\Framework\App\RequestInterface;
use Webkul\Mpshipping\Model\MpshippingmethodFactory;
use Webkul\Mpshipping\Model\MpshippingDistFactory;
use Magento\Customer\Model\Url;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

class Update extends Action
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
     * @var Webkul\Mpshipping\Model\MpshippingDistFactory
     */
    protected $_mpshippingDist;
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
     * @param Context                 $context
     * @param Session                 $customerSession
     * @param MpshippingmethodFactory $shippingmethodFactory
     * @param MpshippingDistFactory       $mpshippingDistFactory
     * @param Url                     $customerUrl
     */

    public function __construct(
        Context $context,
        Session $customerSession,
        MpshippingmethodFactory $shippingmethodFactory,
        MpshippingDistFactory $mpshippingDist,
        \Webkul\Mpshipping\Helper\Data $mpshippingHelperData,
        FormKeyValidator $formKeyValidator,
        Url $customerUrl
    ) {
        parent::__construct($context);
        $this->_session = $customerSession;
        $this->_mpshippingMethod = $shippingmethodFactory;
        $this->_mpshippingDist = $mpshippingDist;
        $this->_mpshippingHelperData = $mpshippingHelperData;
        $this->_formKeyValidator = $formKeyValidator;
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
                $id = $params['id'];
                $partnerId = $this->_mpshippingHelperData->getPartnerId();
                $params['partner_id'] = $partnerId;
                if (!empty($params)) {
                    if ($params['dist_from']<$params['dist_to']) {
                        if ($params['price_from']<$params['price_to']) {
                            $this->checkAndUpdateShipping($params, $partnerId, $id);
                        } else {
                            $this->messageManager->addError(__('Price From can\'t be equal or more than Price To.'));
                        }
                    } else {
                        $this->messageManager->addError(__('Distance From can\'t be equal or more than Distance To.'));
                    }
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath('*/*/view');
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath('*/*/view');
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/view');
    }
    /**
     * checkAndUpdateShipping function
     *
     * @param mixed[] $params
     * @param int $partnerId
     * @param int $id
     * @return void
     */
    protected function checkAndUpdateShipping($params, $partnerId, $id)
    {
        $shippingSet1 = $this->_mpshippingDist->create()->getCollection()
        ->addFieldToFilter('price_from', ['lteq'=>$params['price_from']])
        ->addFieldToFilter('price_to', ['gteq'=>$params['price_to']])
        ->addFieldToFilter('dist_from', ['lteq'=>$params['dist_from']])
        ->addFieldToFilter('dist_to', ['gteq'=>$params['dist_to']])
        ->addFieldToFilter('partner_id', ['eq'=>$partnerId])
        ->addFieldToFilter('entity_id', ['neq'=>$id]);
        $shippingSet2 = $this->_mpshippingDist->create()->getCollection()
                ->addFieldToFilter('price_from', ['lteq'=>$params['price_to']])
                ->addFieldToFilter('price_to', ['gteq'=>$params['price_from']])
                ->addFieldToFilter('dist_from', ['lteq'=>$params['dist_to']])
                ->addFieldToFilter('dist_to', ['gteq'=>$params['dist_from']])
                ->addFieldToFilter('partner_id', ['eq'=>$partnerId])
                ->addFieldToFilter('entity_id', ['neq'=>$id]);
        if (count($shippingSet1) || count($shippingSet2)) {
            $this->messageManager->addError(__('Price range already exist.'));
        } else {
            $this->updateSuperShippingMethodRate($params);
            $this->messageManager->addSuccess(__('Shipping set updated successfully'));
        }
    }
    /**
     * getShippingNameById function
     *
     * @param string $shippingMethodName
     * @return int
     */
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
    /**
     * updateSuperShippingMethodRate function
     *
     * @param mixed[] $wholedata
     * @return void
     */
    public function updateSuperShippingMethodRate($wholedata)
    {
        $wholedata['shipping_method'] = htmlentities($wholedata['shipping_method']);
        $shippingMethodId = $this->getShippingNameById($wholedata['shipping_method']);
        if ($shippingMethodId==0) {
            $mpshippingMethod = $this->_mpshippingMethod->create();
            $mpshippingMethod->setMethodName($wholedata['shipping_method']);
            $savedMethod = $mpshippingMethod->save();
            $shippingMethodId = $savedMethod->getEntityId();
        }
        $wholedata['shipping_method_id'] = $shippingMethodId;
        $shippingSetModel = $this->_mpshippingDist->create();
        $shippingSetModel->setData($wholedata);
        $shippingSetModel->setEntityId($wholedata['id']);
        $shippingSetModel->save();
    }
}
