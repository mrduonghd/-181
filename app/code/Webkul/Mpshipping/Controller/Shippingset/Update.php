<?php
/**
 * Shippingset Controller
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
use Webkul\Mpshipping\Model\MpshippingmethodFactory;
use Webkul\Mpshipping\Model\MpshippingsetFactory;
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
     * @var Webkul\Mpshipping\Model\MpshippingsetFactory
     */
    protected $_mpshippingSet;
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
     * @param MpshippingsetFactory       $MpshippingsetFactory
     * @param Url                     $customerUrl
     */

    public function __construct(
        Context $context,
        Session $customerSession,
        MpshippingmethodFactory $shippingmethodFactory,
        MpshippingsetFactory $shippingsetFactory,
        \Webkul\Mpshipping\Helper\Data $mpshippingHelperData,
        FormKeyValidator $formKeyValidator,
        Url $customerUrl
    ) {
        parent::__construct($context);
        $this->_session = $customerSession;
        $this->_mpshippingMethod = $shippingmethodFactory;
        $this->_mpshippingSet = $shippingsetFactory;
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
                    if ($params['price_from']<$params['price_to']) {
                        $shippingSet1 = $this->_mpshippingSet->create()->getCollection()
                                     ->addFieldToFilter('price_from', ['lteq'=>$params['price_from']])
                                     ->addFieldToFilter('price_to', ['gteq'=>$params['price_to']])
                                     ->addFieldToFilter('partner_id', ['eq'=>$partnerId])
                                     ->addFieldToFilter('entity_id', ['neq'=>$id]);
                        $shippingSet2 = $this->_mpshippingSet->create()->getCollection()
                                     ->addFieldToFilter('price_from', ['lteq'=>$params['price_to']])
                                     ->addFieldToFilter('price_to', ['gteq'=>$params['price_from']])
                                     ->addFieldToFilter('partner_id', ['eq'=>$partnerId])
                                     ->addFieldToFilter('entity_id', ['neq'=>$id]);
                        if (count($shippingSet1) || count($shippingSet2)) {
                            $this->messageManager->addError(__('Price range already exist.'));
                        } else {
                            $this->updateSuperShippingMethodRate($params);
                            $this->messageManager->addSuccess(__('Shipping set updated successfully'));
                        }
                    } else {
                        $this->messageManager->addError(__('Price From can\'t be equal or more than Price To.'));
                    }
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath('mpshipping/shippingset/view');
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath('mpshipping/shippingset/view');
        }
        return $this->resultRedirectFactory->create()->setPath('mpshipping/shippingset/view');
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

    public function updateSuperShippingMethodRate($wholedata)
    {
        if ($wholedata['shipping_type'] == 'free') {
            $wholedata['price'] =0;
        }
        $wholedata['shipping_method'] = htmlentities($wholedata['shipping_method']);
        $shippingMethodId = $this->getShippingNameById($wholedata['shipping_method']);
        if ($shippingMethodId==0) {
            $mpshippingMethod = $this->_mpshippingMethod->create();
            $mpshippingMethod->setMethodName($wholedata['shipping_method']);
            $savedMethod = $mpshippingMethod->save();
            $shippingMethodId = $savedMethod->getEntityId();
        }
        $wholedata['shipping_method_id'] = $shippingMethodId;
        $shippingSetModel = $this->_mpshippingSet->create();
        $shippingSetModel->setData($wholedata);
        $shippingSetModel->setEntityId($wholedata['id']);
        $shippingSetModel->save();
    }
}
