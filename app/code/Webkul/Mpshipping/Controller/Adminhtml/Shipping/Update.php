<?php

/**
 * Mpshipping Admin Shipping update Controller.
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpshipping\Controller\Adminhtml\Shipping;

use Magento\Backend\App\Action;
use Webkul\Mpshipping\Model\MpshippingmethodFactory;
use Webkul\Mpshipping\Model\MpshippingFactory;

class Update extends \Magento\Backend\App\Action
{
    /**
     * @var MpshippingmethodFactory
     */
    protected $mpshippingMethod;
    /**
     * @var MpshippingFactory
     */
    protected $mpshippingModel;

    /**
     * @param Action\Context                             $context
     * @param MpshippingmethodFactory                    $mpshippingMethod
     * @param MpshippingFactory                          $mpshippingModel
     */
    public function __construct(
        Action\Context $context,
        MpshippingmethodFactory $mpshippingMethod,
        MpshippingFactory $mpshippingModel
    ) {
        parent::__construct($context);
        $this->mpshippingMethod = $mpshippingMethod;
        $this->mpshippingModel = $mpshippingModel;
    }

    /**
     * @return bool
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->getRequest()->isPost()) {
            try {
                if (!$this->_formKeyValidator->validate($this->getRequest())) {
                    return $this->resultRedirectFactory->create()->setPath('*/*/index');
                }
                $params = $this->getRequest()->getParams();
                if (!empty($params)) {
                    $shippingData = $this->mpshippingModel
                      ->create()
                      ->load($params['mpshipping_id']);
                    $shippingMethodId = $this->getShippingIdByName($params['shipping_method']);
                    if ($shippingMethodId==0) {
                        $mpshippingMethod = $this->mpshippingMethod->create();
                        $mpshippingMethod->setMethodName($params['shipping_method']);
                        $savedMethod = $mpshippingMethod->save();
                        $shippingMethodId = $savedMethod->getEntityId();
                    }
                    $params['shipping_method_id'] = $shippingMethodId;
                    $shippingCollection = $this->mpshippingModel->create()
                        ->getCollection()
                        ->addFieldToFilter('partner_id', $params['partner_id'])
                        ->addFieldToFilter('dest_country_id', $params['dest_country_id'])
                        ->addFieldToFilter('dest_region_id', $params['dest_region_id'])
                        ->addFieldToFilter('dest_zip', ['gteq'=>$params['dest_zip']])
                        ->addFieldToFilter('dest_zip_to', ['lteq' =>$params['dest_zip_to']])
                        ->addFieldToFilter('weight_from', ['lteq' =>$params['weight_from']])
                        ->addFieldToFilter('weight_to', ['gteq' =>$params['weight_to']])
                        ->addFieldToFilter('shipping_method_id', $shippingMethodId)
                        ->addFieldToFilter('mpshipping_id', ['neq' => $params['mpshipping_id']]);
                    if ($params['is_range'] == 'no') {
                        $shippingCollection->addFieldToFilter('is_range', ['eq'=>$params['is_range']]);
                        $shippingCollection->addFieldToFilter('zipcode', ['eq'=>$params['zipcode']]);
                    }
                    if ($shippingCollection->getSize()) {
                        $this->messageManager->addError(
                            __('Shipping rule already exist for the given range for seller.')
                        );
                        return $this->resultRedirectFactory->create()->setPath('*/*/index');
                    }
                    $shippingData->addData($params);
                    $shippingData->setMpShippingId($params['mpshipping_id'])->save();
                    $this->messageManager->addSuccess(__('Your shipping detail has been successfully updated.'));
                    return $this->resultRedirectFactory->create()->setPath('*/*/index');
                } else {
                    $this->messageManager->addError(__('No record Found!'));
                    return $this->resultRedirectFactory->create()->setPath('*/*/index');
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }

    /**
     * [getShippingIdByName Get Shipping Method Id by method name]
     * @param  string $shippingMethodName
     * @return int
     */
    public function getShippingIdByName($shippingMethodName)
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
     * [_isAllowed To check the allowed authorization]
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Mpshipping::mpshipping');
    }
}
