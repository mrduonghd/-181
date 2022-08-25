<?php

/**
 * Mpshipping Admin Shipping Save Controller.
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
use Magento\MediaStorage\Model\File\UploaderFactory;

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
     * @var Webkul\Mpshipping\Model\Mpshipping
     */
    protected $_mpshipping;
    /**
     * @var Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploader;
    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $_csvReader;

    /**
     * @param Action\Context                             $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry                $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        MpshippingmethodFactory $shippingmethodFactory,
        MpshippingFactory $mpshipping,
        UploaderFactory $fileUploader,
        \Magento\Framework\File\Csv $csvReader
    ) {
        parent::__construct($context);
        $this->_resultPageFactory = $resultPageFactory;
        $this->_mpshippingMethod = $shippingmethodFactory;
        $this->_mpshipping = $mpshipping;
        $this->_fileUploader = $fileUploader;
        $this->_csvReader = $csvReader;
    }

    /**
     * Check for is allowed.
     *
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
                $uploader = $this->_fileUploader->create(
                    ['fileId' => 'import_file']
                );
                $result = $uploader->validateFile();
                $rows = [];
                $file = $result['tmp_name'];
                $fileNameArray = explode('.', $result['name']);
                $ext = end($fileNameArray);
                $status = true;
                $totalSaved = 0;
                $totalUpdated = 0;
                $headerArray = ['country_code',"region_id",'zip','zip_to','price','weight_from',
                'weight_to','shipping_method','seller_id','numeric_zipcode','alphanumeric_zipcode'];
                if ($file != '' && $ext == 'csv') {
                    $csvFileData = $this->_csvReader->getData($file);
                    $partnerid = 0;
                    $count = 0;
                    foreach ($csvFileData as $key => $rowData) {
                        if ($count==0) {
                            $this->getCsvFileData($rowData, $count, $headerArray);
                            $count++;
                            $data = $rowData;
                        } else {
                            $wholedata = $this->getForeachData($rowData, $data);
                            list($updatedWholedata, $errors) = $this->validateCsvDataToSave($wholedata);
                            $updatedWholedata['shipping_method'] = htmlentities($updatedWholedata['shipping_method']);
                            $rowSaved = $this->getUpdateWholeData(
                                $errors,
                                $updatedWholedata,
                                $totalSaved,
                                $totalUpdated
                            );
                                $totalSaved = $rowSaved[0];
                                $totalUpdated = $rowSaved[1];
                        }
                    }
                    $this->getCount($rows, $count, $totalSaved, $totalUpdated);
                    
                    return $this->resultRedirectFactory->create()->setPath('*/*/index');
                } else {
                    $this->messageManager->addError(__('Please upload CSV file'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Mpshipping::mpshipping');
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

    public function addDataToCollection($temp, $updatedWholedata, $shippingMethodId, $partnerId)
    {
        $updated =0;
        $saved = 0;
        $collection = $this->_mpshipping->create()
            ->getCollection()
            ->addFieldToFilter('partner_id', $partnerId)
            ->addFieldToFilter('dest_country_id', $updatedWholedata['country_code'])
            ->addFieldToFilter('dest_region_id', $updatedWholedata['region_id'])
            ->addFieldToFilter('dest_zip', ['gteq'=>$updatedWholedata['zip']])
            ->addFieldToFilter('dest_zip_to', ['lteq' =>$updatedWholedata['zip_to']])
            ->addFieldToFilter('weight_from', ['lteq' =>$updatedWholedata['weight_from']])
            ->addFieldToFilter('weight_to', ['gteq' =>$updatedWholedata['weight_to']])
            ->addFieldToFilter('shipping_method_id', $shippingMethodId);
        if ($temp['is_range'] == 'no') {
            $collection->addFieldToFilter('is_range', ['eq'=>$temp['is_range']]);
            $collection->addFieldToFilter('zipcode', ['eq'=>$updatedWholedata['alphanumeric_zipcode']]);
        }
        if ($collection->getSize() > 0) {
            ++$updated;
        } else {
            $shippingModel = $this->_mpshipping->create();
            $shippingModel->setData($temp)->save();
            ++$saved;
        }
        return [$saved, $updated];
    }

    public function calculateShippingMethodId($sellerId)
    {
        $shippingMethodId = $this->getShippingNameById($sellerId);
        if ($shippingMethodId==0) {
            $mpshippingMethod = $this->_mpshippingMethod->create();
            $mpshippingMethod->setMethodName($sellerId);
            $savedMethod = $mpshippingMethod->save();
            $shippingMethodId = $savedMethod->getEntityId();
        }
        return $shippingMethodId;
    }
    public function validateCsvDataToSave($wholedata)
    {
        $data = [];
        $errors = [];
        foreach ($wholedata as $key => $value) {

            switch ($key) {
                case 'shipping_method':
                    $data[$key] = $this->caseShippingMethod($value, $key);
                    break;
                case 'country_code':
                    if (!is_string(trim($value))) {
                        $errors[] = __('Country code should be a string %1', $value);
                    } elseif ($value == '') {
                        $errors[] = __('Country code can not be empty');
                    } else {
                        $data[$key] = $value;
                    }
                    break;
                case 'region_id':
                    if ($value == '') {
                        $errors[] = __('Region Id can not be empty');
                    } else {
                        $data[$key] = $value;
                    }
                    break;
                case 'zip':
                    if ($value == '') {
                        $errors[] = __('Zip field can not be empty');
                    } elseif (!preg_match('/^([0-9*])+?[0-9.]*$/', $value)) {
                        $errors[] = __('Zip field from should be a numeric or * value %1', $value);
                    } else {
                        $data[$key] = $value;
                    }
                    break;
                case 'zip_to':
                    $data[$key] = $this->caseZipTo($value, $key);
                    break;
                case 'price':
                    if ($value == '') {
                        $errors[] = __('Price can not be empty');
                    } elseif (!preg_match('/^([0-9])+?[0-9.]*$/', $value)) {
                        $errors[] = __('Not a valid value for price %1', $value);
                    } else {
                        $data[$key] = $value;
                    }
                    break;
                case 'weight_from':
                    $data[$key] = $this->caseWeightFrom($value, $key);
                    break;
                case 'weight_to':
                    $data[$key] = $this->caseWeightTo($value, $key);
                    break;
                case 'seller_id':
                    if ($value == '') {
                        $errors[] = __('Seller Id can not be empty');
                    } else {
                        $data[$key] = $value;
                    }
                    break;
                case 'numeric_zipcode':
                    if ($value == '') {
                        $errors[] = __('Numeric Zipcode value can not be empty');
                    } else {
                        $data[$key] = $value;
                    }
                    break;
                case 'alphanumeric_zipcode':
                    if ($value == '' && (strtolower($wholedata['numeric_zipcode']) == 'no')) {
                         $errors[] = __('Alphanumeric Zipcode can not be empty');
                    } else {
                        $data[$key] = $value;
                    }
                    break;
            }
        }
        return [$data, $errors];
    }

    /**
     * Get Shipping Method Data
     *
     * @param  $value  [$value description]
     * @param  $key    [$key description]
     *
     * @return $data   [return description]
     */
    public function caseShippingMethod($value, $key)
    {
        if (!is_string(trim($value))) {
            $errors[] = __('Shipping Method should be a string %1', $value);
        } elseif ($value == '') {
            $errors[] = __('Shipping Method can not be empty');
        } else {
            $data[$key] = $value;
        }
        return $data[$key];
    }

    /**
     * Get Zip To Data
     *
     * @param  $value  [$value description]
     * @param  $key    [$key description]
     *
     * @return $data   [return description]
     */
    public function caseZipTo($value, $key)
    {
        if ($value == '') {
            $errors[] = __('Zip to field can not be empty');
        } elseif (!preg_match('/^([0-9*])+?[0-9.]*$/', $value)) {
            $errors[] = __('Zip to field from should be a numeric or * value %1', $value);
            if (isset($data['zip'])) {
                if ($data['zip'] >= $value) {
                    $errors[] = __('Zip To field should be greater then Zip From field');
                }
            } else {
                $errors[] = __('Zip field can not be empty');
            }
        } else {
            $data[$key] = $value;
        }
        return $data[$key];
    }

    /**
     * Get Weight To Data
     *
     * @param  $value  [$value description]
     * @param  $key    [$key description]
     *
     * @return $data   [return description]
     */
    public function caseWeightTo($value, $key)
    {
        if ($value == '') {
            $errors[] = __('Weight To can not be empty');
        } elseif (!preg_match('/^([0-9])+?[0-9.]*$/', $value)) {
            $errors[] = __('Not a valid value for weight to field %1', $value);
            if (isset($data['weight_from'])) {
                if ($data['weight_from'] >= $value) {
                    $errors[] = __('Weight to should be greater then weight from field');
                }
            } else {
                $errors[] = __('Weight From can not be empty');
            }
        } else {
            $data[$key] = $value;
        }
        return $data[$key];
    }

    /**
     * Get Weight From Data
     *
     * @param  $value  [$value description]
     * @param  $key    [$key description]
     *
     * @return $data   [return description]
     */
    public function caseWeightFrom($value, $key)
    {
        if ($value == '') {
            $errors[] = __('Weight From can not be empty');
        } elseif (!preg_match('/^([0-9])+?[0-9.]*$/', $value)) {
            $errors[] = __('Not a valid value for weight from field %1', $value);
        } else {
            $data[$key] = $value;
        }
        return $data[$key];
    }

    /**
     * Get Count Of Data Save or Update
     *
     * @param $rows          [$rows description]
     * @param $count         [$count description]
     * @param $totalSaved    [$totalSaved description]
     * @param $totalUpdated  [$totalUpdated description]
     */
    public function getCount($rows, $count, $totalSaved, $totalUpdated)
    {
        if (count($rows)) {
            $this->messageManager->addError(
                __(
                    'Following rows are not valid rows : %1',
                    implode(',', $rows)
                )
            );
        }
        if (($count - 1) <= 1) {
            if ($totalSaved) {
                $this->messageManager
                ->addSuccess(
                    __('%1 Row(s) shipping detail has been successfully saved', $totalSaved)
                );
            }
            if ($totalUpdated) {
                $this->messageManager
                ->addNotice(
                    __('%1 Row(s) shipping rule already exist for the given range.', $totalUpdated)
                );
            }
        }
    }

    /**
     * Get Csv File Data
     *
     * @param $rowData          [$rowData description]
     * @param $count            [$count description]
     * @param $headerArray      [$headerArray description]
     */
    public function getCsvFileData($rowData, $count, $headerArray)
    {
        if (count($rowData) < 11) {
            $this->messageManager->addError(__('CSV file is not a valid file!'));
            return $this->resultRedirectFactory->create()->setPath('*/*/index');
        } else {
            $status =($headerArray === $rowData);
            if (!$status) {
                $this->messageManager->addError(__('Please write the correct header formation of CSV file!'));
                return $this->resultRedirectFactory->create()->setPath('*/*/index');
            }
        }
    }

    /**
     * Get Update Data
     *
     * @param $errors            [$errors description]
     * @param $updatedWholedata  [$updatedWholedata description]
     * @param $totalSaved        [$totalSaved description]
     * @param $totalUpdated      [$totalUpdated description]
     *
     * @return                   [$wholedata description]
     */
    public function getUpdateWholeData($errors, $updatedWholedata, $totalSaved, $totalUpdated)
    {
        if (empty($errors)) {
            $shippingMethodId = $this->calculateShippingMethodId(
                $updatedWholedata['shipping_method']
            );
            $partnerId = $updatedWholedata['seller_id'];
            $temp = [
            'dest_country_id' => $updatedWholedata['country_code'],
            'dest_region_id' => htmlentities($updatedWholedata['region_id']),
            'dest_zip' => htmlentities($updatedWholedata['zip']),
            'dest_zip_to' => htmlentities($updatedWholedata['zip_to']),
            'price' => $updatedWholedata['price'],
            'weight_from' => $updatedWholedata['weight_from'],
            'weight_to' => $updatedWholedata['weight_to'],
            'shipping_method_id' => $shippingMethodId,
            'partner_id' => $partnerId,
            'is_range' =>strtolower($updatedWholedata['numeric_zipcode']),
            'zipcode' => htmlentities($updatedWholedata['alphanumeric_zipcode']),
            ];
            list($saved, $updated) = $this->
            addDataToCollection($temp, $updatedWholedata, $shippingMethodId, $partnerId);
            $totalSaved += $saved;
            $totalUpdated += $updated;
        } else {
            $rows[] = $key.':'.$errors[0];
        }
        return [$totalSaved, $totalUpdated];
    }

    /**
     * Get Data by Foreach loop
     *
     * @param $rowData   [$row description]
     * @param $data      [$data description]
     *
     * @return       [$wholedata description]
     */
    public function getForeachData($rowData, $data)
    {
        foreach ($rowData as $filekey => $filevalue) {
            $wholedata[$data[$filekey]] = $filevalue;
        }
        return $wholedata;
    }
}
