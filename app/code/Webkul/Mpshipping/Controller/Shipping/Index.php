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
use Magento\MediaStorage\Model\File\UploaderFactory;
use Webkul\Mpshipping\Model\MpshippingFactory;

class Index extends Action
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
     * @var \Magento\Framework\File\Csv
     */
    protected $_csvReader;
    /**
     * @var Magento\Customer\Model\Url
     */
    protected $_customerUrl;
    /**
     * @var Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploader;
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
        \Magento\Framework\File\Csv $csvReader,
        Url $customerUrl,
        UploaderFactory $fileUploader,
        \Webkul\Mpshipping\Helper\Data $mpshippingHelperData,
        MpshippingFactory $mpshippingModel
    ) {
        parent::__construct($context);
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_mpshippingMethod = $shippingmethodFactory;
        $this->_csvReader = $csvReader;
        $this->_customerUrl = $customerUrl;
        $this->_fileUploader = $fileUploader;
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
     * save Shipping rate.
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
                $uploader = $this->_fileUploader->create(
                    ['fileId' => 'shippingfile']
                );
                $rows = [];
                $result = $uploader->validateFile();
                $wholedata = [];
                $file = $result['tmp_name'];
                $fileNameArray = explode('.', $result['name']);
                $ext = end($fileNameArray);
                $status = true;
                $totalSaved = 0;
                $totalUpdated = 0;
                $headerArray = ['country_code',"region_id",'zip','zip_to','price',
                'weight_from','weight_to','shipping_method','numeric_zipcode','alphanumeric_zipcode'];
                if ($file != '' && $ext == 'csv') {
                    $csvFileData = $this->_csvReader->getData($file);
                    $count = 0;
                    foreach ($csvFileData as $key => $row) {
                        if ($count==0) {
                            $this->getCsvFileData($row, $count, $headerArray);
                            $count++;
                            $data = $row;
                        } else {
                            $wholedata = $this->getForeachData($row, $data);
                            $partnerid = $this->_mpshippingHelperData->getPartnerId();
                            list($updatedWholedata, $errors) = $this->validateCsvDataToSave($wholedata);
                            $rowSaved = $this->getUpdateWholeData(
                                $errors,
                                $updatedWholedata,
                                $totalSaved,
                                $totalUpdated,
                                $partnerid
                            );
                                $totalSaved = $rowSaved[0];
                                $totalUpdated = $rowSaved[1];
                        }
                    }
                    $this->getCount($rows, $count, $totalSaved, $totalUpdated);
                    
                } else {
                    $this->messageManager->addError(__('Please upload Csv file'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $this->resultRedirectFactory->create()->setPath('mpshipping/shipping/view');
            }
        }
        return $this->resultRedirectFactory->create()->setPath('mpshipping/shipping/view');
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

    public function addShippingMethodRate($updatedWholedata, $partnerId)
    {
        $updated =0;
        $saved = 0;
        $updatedWholedata['shipping_method'] = htmlentities($updatedWholedata['shipping_method']);
        $shippingMethodId = $this->getShippingNameById($updatedWholedata['shipping_method']);
        if ($shippingMethodId==0) {
            $mpshippingMethod = $this->_mpshippingMethod->create();
            $mpshippingMethod->setMethodName($updatedWholedata['shipping_method']);
            $savedMethod = $mpshippingMethod->save();
            $shippingMethodId = $savedMethod->getEntityId();
        }
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
        $shippingCollection = $this->_mpshippingModel
            ->create()
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
            $shippingCollection->addFieldToFilter('is_range', ['eq'=>$temp['is_range']]);
            $shippingCollection->addFieldToFilter('zipcode', ['eq'=>$updatedWholedata['alphanumeric_zipcode']]);
        }
        if ($shippingCollection->getSize()) {
            ++$updated;
        } else {
            $shippingModel = $this->_mpshippingModel
                ->create();
            $shippingModel->setData($temp);
            $shippingModel->save();
            ++$saved;
        }
        return [$saved, $updated];
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
                        $errors[] = __('Zip field can not be empty %1', $value);
                    } elseif ((strtolower($wholedata['numeric_zipcode']) == 'yes')
                    && !preg_match('/^([0-9*])+?[0-9.]*$/', $value)) {
                        $errors[] = __('Zip field from should be a numeric or * value %1', $value);
                    } else {
                        $data[$key] = $value;
                    }
                    break;
                case 'zip_to':
                    $data[$key] = $this->caseZipTo($value, $key, $wholedata);
                    break;
                case 'price':
                    if ($value == '') {
                        $errors[] = __('Price can not be empty %1', $value);
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
    public function caseZipTo($value, $key, $wholedata)
    {
        if ($value == '') {
            $errors[] = __('Zip to field can not be empty %1', $value);
        } elseif ((strtolower($wholedata['numeric_zipcode']) == 'yes')
        && !preg_match('/^([0-9*])+?[0-9.]*$/', $value)) {
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
            $errors[] = __('Weight To can not be empty %1', $value);
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
            $errors[] = __('Weight From can not be empty %1', $value);
        } elseif (!preg_match('/^([0-9])+?[0-9.]*$/', $value)) {
            $errors[] = __('Not a valid value for weight from field %1', $value);
        } else {
            $data[$key] = $value;
        }
        return $data[$key];
    }

    /**
     * Get Csv File Data
     *
     * @param $rowData          [$rowData description]
     * @param $count            [$count description]
     * @param $headerArray      [$headerArray description]
     */
    public function getCsvFileData($row, $count, $headerArray)
    {
        if (count($row) < 10) {
            $this->messageManager->addError(__('CSV file is not a valid file!'));
            return $this->resultRedirectFactory
            ->create()->setPath(
                'mpshipping/shipping/view',
                ['_secure'=>true]
            );
        } else {
            $status =($headerArray === $row);
            if (!$status) {
                $this->messageManager->addError(__('Please write the correct header formation of CSV file!'));
                return $this->resultRedirectFactory
                ->create()->setPath(
                    'mpshipping/shipping/view',
                    ['_secure'=>true]
                );
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
    public function getUpdateWholeData($errors, $updatedWholedata, $totalSaved, $totalUpdated, $partnerid)
    {
        if (empty($errors)) {
            list($saved, $updated) = $this->addShippingMethodRate($updatedWholedata, $partnerid);
            $totalSaved +=$saved;
            $totalUpdated +=$updated;
        } else {
            $rows[] = $key.':'.$errors[0];
        }
        return [$totalSaved, $totalUpdated];
    }

    /**
     * Get Count Of Data Save or Update
     *
     * @param $rows          [$rows description]
     * @param $count         [$count description]
     * @param $totalSaved    [$totalSaved description]
     * @param $totalUpdated  [$totalUpdated description]
     *
     * @return               [return description]
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
     * Get Data by Foreach loop
     *
     * @param $row   [$row description]
     * @param $data  [$data description]
     *
     * @return       [$wholedata description]
     */
    public function getForeachData($row, $data)
    {
        foreach ($row as $filekey => $filevalue) {
            $wholedata[$data[$filekey]] = $filevalue;
        }
        return $wholedata;
    }
}
