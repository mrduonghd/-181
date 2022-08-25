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

namespace Webkul\Mpshipping\Controller\Shippingset ;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Webkul\Mpshipping\Model\MpshippingmethodFactory;
use Webkul\Mpshipping\Model\MpshippingsetFactory;
use Magento\Customer\Model\Url;
use Webkul\Mpshipping\Model\MpshippingFactory;

class Save extends Action
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
     * @var Webkul\Mpshipping\Model\MpshippingsetFactory
     */
    protected $_mpshippingSet;
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
        MpshippingsetFactory $shippingsetFactory,
        Url $customerUrl,
        \Webkul\Mpshipping\Helper\Data $mpshippingHelperData,
        MpshippingFactory $mpshippingModel
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_mpshippingMethod = $shippingmethodFactory;
        $this->_mpshippingSet = $shippingsetFactory;
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
     * save super set Shipping rate.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->getRequest()->isPost()) {
            if (!$this->_formKeyValidator->validate($this->getRequest())) {
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/view',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
            }
            $wholedata = $this->getRequest()->getParams();
            $partnerId = $this->_mpshippingHelperData->getPartnerId();
            if ($wholedata['price_from']<$wholedata['price_to']) {
                $shippingSet1 = $this->_mpshippingSet->create()->getCollection()
                                ->addFieldToFilter('price_from', ['lteq'=>$wholedata['price_from']])
                                ->addFieldToFilter('price_to', ['gteq'=>$wholedata['price_to']])
                                ->addFieldToFilter('partner_id', ['eq'=>$partnerId]);
                $shippingSet2 = $this->_mpshippingSet->create()->getCollection()
                                ->addFieldToFilter('price_from', ['lteq'=>$wholedata['price_to']])
                                ->addFieldToFilter('price_to', ['gteq'=>$wholedata['price_from']])
                                ->addFieldToFilter('partner_id', ['eq'=>$partnerId]);
                if (count($shippingSet1) || count($shippingSet2)) {
                    $this->messageManager->addError(__('Price range already exist.'));
                } else {
                    $this->addSuperShippingMethodRate($wholedata, $partnerId);
                    $this->messageManager->addSuccess(__('Shipping Set Details Saved Successfully'));
                }
            } else {
                $this->messageManager->addError(__('Price From can\'t be equal or more than Price To.'));
            }
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

    public function addSuperShippingMethodRate($wholedata, $partnerId)
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
        $wholedata['partner_id'] = $partnerId;
        $shippingSetModel = $this->_mpshippingSet->create();
        $shippingSetModel->setData($wholedata);
        $shippingSetModel->save();
    }
}
