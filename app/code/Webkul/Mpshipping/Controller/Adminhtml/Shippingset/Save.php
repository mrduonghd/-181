<?php

/**
 * Mpshipping Admin Shippingset Save Controller.
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpshipping\Controller\Adminhtml\Shippingset;

use Magento\Backend\App\Action;
use Webkul\Mpshipping\Model\MpshippingmethodFactory;
use Webkul\Mpshipping\Model\MpshippingsetFactory;

class Save extends \Magento\Backend\App\Action
{
    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;
    /**
     * @var Webkul\Mpshipping\Model\MpshippingmethodFactory
     */
    protected $_mpshippingMethod;
    /**
     * @var Webkul\Mpshipping\Model\Mpshippingset
     */
    protected $_mpshippingset;

    /**
     * @param Action\Context                             $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry                $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        MpshippingmethodFactory $shippingmethodFactory,
        MpshippingsetFactory $mpshippingset
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_mpshippingMethod = $shippingmethodFactory;
        $this->_mpshippingset = $mpshippingset;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath('*/*/index');
                }
                $wholedata = $this->getRequest()->getParams();
                $wholedata['method_name'] = htmlentities($wholedata['method_name']);
                if ($wholedata['price_from']<$wholedata['price_to']) {
                    $shippingSet1 = $this->_mpshippingset->create()->getCollection()
                                  ->addFieldToFilter('price_from', ['lteq'=>$wholedata['price_from']])
                                  ->addFieldToFilter('price_to', ['gteq'=>$wholedata['price_to']])
                                  ->addFieldToFilter('partner_id', ['eq'=>$wholedata['partner_id']]);
                    $shippingSet2 = $this->_mpshippingset->create()->getCollection()
                                  ->addFieldToFilter('price_from', ['lteq'=>$wholedata['price_to']])
                                  ->addFieldToFilter('price_to', ['gteq'=>$wholedata['price_from']])
                                  ->addFieldToFilter('partner_id', ['eq'=>$wholedata['partner_id']]);
                    if (isset($wholedata['entity_id'])) {
                        $shippingSet1->addFieldToFilter("entity_id", ["neq" =>$wholedata['entity_id']]);
                        $shippingSet2->addFieldToFilter("entity_id", ["neq" =>$wholedata['entity_id']]);
                    }
                    if (count($shippingSet1) || count($shippingSet2)) {
                        $this->messageManager->addError(__('Price range already exist.'));
                    } else {
                        $this->addSuperShippingMethodRate($wholedata);
                        if (!isset($wholedata['entity_id'])) {
                            $this->messageManager->addSuccess(__('Shipping Set Details Saved Successfully'));
                        } else {
                            $this->messageManager->addSuccess(__('Shipping Set Details Successfully Updated'));
                        }
                    }
                } else {
                    $this->messageManager->addError(__('Price From can\'t be equal or more than Price To.'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/index');
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

    public function addSuperShippingMethodRate($wholedata)
    {
        if ($wholedata['shipping_type'] == 'free') {
            $wholedata['price'] =0;
        }
        $shippingMethodId = $this->getShippingNameById($wholedata['method_name']);
        if ($shippingMethodId==0) {
            $mpshippingMethod = $this->_mpshippingMethod->create();
            $mpshippingMethod->setMethodName($wholedata['method_name']);
            $savedMethod = $mpshippingMethod->save();
            $shippingMethodId = $savedMethod->getEntityId();
        }
        $wholedata['shipping_method_id'] = $shippingMethodId;
        $shippingSetModel = $this->_mpshippingset->create();
        if (isset($wholedata['entity_id'])) {
            $shippingSetModel->setData($wholedata)->setEntityId($wholedata['entity_id']);
        } else {
            $shippingSetModel->setData($wholedata);
        }
        $shippingSetModel->save();
    }

    /**
     * check permission
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Mpshipping::mpshippingset');
    }
}
