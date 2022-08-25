<?php

/**
 * Mpshipping Admin Distanceset Save Controller.
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpshipping\Controller\Adminhtml\Distanceset;

use Magento\Backend\App\Action;
use Webkul\Mpshipping\Model\MpshippingmethodFactory;
use Webkul\Mpshipping\Model\MpshippingDistFactory;

class Save extends \Webkul\Mpshipping\Controller\Adminhtml\Distanceset
{
    /**
     * @var MpshippingmethodFactory
     */
    protected $mpshippingMethod;
    /**
     * @var MpshippingDistFactory
     */
    protected $mpshippingDist;

    /**
     * @param Action\Context $context
     * @param MpshippingmethodFactory $mpshippingMethod
     * @param MpshippingDistFactory $registry
     */
    public function __construct(
        Action\Context $context,
        MpshippingmethodFactory $shippingmethodFactory,
        MpshippingDistFactory $mpshippingDist
    ) {
        parent::__construct($context);
        $this->mpshippingMethod = $shippingmethodFactory;
        $this->mpshippingDist = $mpshippingDist;
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
                if ($wholedata['dist_from']<$wholedata['dist_to']) {
                    if ($wholedata['price_from']<$wholedata['price_to']) {
                        $this->checkAndUpdateShipping($wholedata);
                    } else {
                        $this->messageManager->addError(__('Price From can\'t be equal or more than Price To.'));
                    }
                } else {
                    $this->messageManager->addError(__('Distance From can\'t be equal or more than Distance To.'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }
    /**
     * checkAndUpdateShipping function
     *
     * @param mixed[] $wholedata
     * @return void
     */
    protected function checkAndUpdateShipping($wholedata)
    {

        $shippingSet1 = $this->mpshippingDist->create()->getCollection()
        ->addFieldToFilter('price_from', ['lteq'=>$wholedata['price_from']])
        ->addFieldToFilter('price_to', ['gteq'=>$wholedata['price_to']])
        ->addFieldToFilter('dist_from', ['lteq'=>$wholedata['dist_from']])
        ->addFieldToFilter('dist_to', ['gteq'=>$wholedata['dist_to']])
        ->addFieldToFilter('partner_id', ['eq'=>$wholedata['partner_id']]);
        $shippingSet2 = $this->mpshippingDist->create()->getCollection()
                ->addFieldToFilter('price_from', ['lteq'=>$wholedata['price_to']])
                ->addFieldToFilter('price_to', ['gteq'=>$wholedata['price_from']])
                ->addFieldToFilter('dist_from', ['lteq'=>$wholedata['dist_to']])
                ->addFieldToFilter('dist_to', ['gteq'=>$wholedata['dist_from']])
                ->addFieldToFilter('partner_id', ['eq'=>$wholedata['partner_id']]);
        if (isset($wholedata['entity_id'])) {
            $shippingSet1->addFieldToFilter("entity_id", ["neq" =>$wholedata['entity_id']]);
            $shippingSet2->addFieldToFilter("entity_id", ["neq" =>$wholedata['entity_id']]);
        }
        if (count($shippingSet1) || count($shippingSet2)) {
            $this->messageManager->addError(__('Price or Distance range already exist.'));
        } else {
            $this->addShippingMethodData($wholedata);
            if (!isset($wholedata['entity_id'])) {
                $this->messageManager->addSuccess(__('Shipping Rule Saved Successfully'));
            } else {
                $this->messageManager->addSuccess(__('Shipping Rule Successfully Updated'));
            }
        }
    }
    /**
     * getShippingNameById function used to get Method name by method Id
     *
     * @param string $shippingMethodName
     * @return int
     */
    public function getShippingNameById($shippingMethodName)
    {
        $entityId = 0;
        $shippingMethodModel = $this->mpshippingMethod->create()
            ->getCollection()
            ->addFieldToFilter('method_name', $shippingMethodName);
        foreach ($shippingMethodModel as $shippingMethod) {
            $entityId = $shippingMethod->getEntityId();
        }
        return $entityId;
    }
    /**
     * addShippingMethodData function is used to save shipping rule.
     *
     * @param mixed[] $wholedata
     * @return void
     */
    public function addShippingMethodData($wholedata)
    {
        $shippingMethodId = $this->getShippingNameById($wholedata['method_name']);
        if ($shippingMethodId==0) {
            $mpshippingMethod = $this->mpshippingMethod->create();
            $mpshippingMethod->setMethodName($wholedata['method_name']);
            $savedMethod = $mpshippingMethod->save();
            $shippingMethodId = $savedMethod->getEntityId();
        }
        $wholedata['shipping_method_id'] = $shippingMethodId;
        $shippingSetModel = $this->mpshippingDist->create();
        if (isset($wholedata['entity_id'])) {
            $shippingSetModel->setData($wholedata)->setEntityId($wholedata['entity_id']);
        } else {
            $shippingSetModel->setData($wholedata);
        }
        $shippingSetModel->save();
    }
}
