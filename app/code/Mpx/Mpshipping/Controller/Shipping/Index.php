<?php
/**
 * Mpx Software
 *
 * @category  Mpx
 * @package   Mpx_Mpshipping
 * @author    Mpx
 */
namespace Mpx\Mpshipping\Controller\Shipping;

/**
 * Mpx Mpshipping import CSV
 */
class Index extends \Webkul\Mpshipping\Controller\Shipping\Index
{

    const COUNTRY_CODE = "JP";
    const REGION_ID = "*";
    const WEIGHT_FROM = "0";
    const WEIGHT_TO = "999";
    const NUMERIC_ZIPCODE = "yes";
    const ALPHANUMERIC_ZIPCODE = "";

    /**
     * @var bool
     */
    protected $isError = false;

    /**
     * Save Shipping rate.
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
                $headerArray = ['zip','zip_to','price', 'shipping_method'];
                if ($file != '' && $ext == 'csv') {
                    $csvFileData = $this->_csvReader->getData($file);
                    if (count($csvFileData) <= 1){
                        $this->messageManager->addError(__('This file Csv is empty please try another one'));
                        return $this->resultRedirectFactory->create()->setPath('mpshipping/shipping/view');
                    }
                    $count = 0;
                    foreach ($csvFileData as $key => $row) {
                        $rowDataEmty = false;
                        foreach ($row as $rowData) {
                           if($rowData != ''){
                               $rowDataEmty = true;
                               break;
                           }
                        }
                        if(!$rowDataEmty){
                            continue;
                        }
                        if ($count==0) {
                            $headerData = $this->getCsvFileData($row, $count, $headerArray);
                            if($headerData ){
                                return $this->resultRedirectFactory
                                    ->create()->setPath(
                                        'mpshipping/shipping/view',
                                        ['_secure'=>true]
                                    );
                            }
                            $count++;
                            $data = $row;
                        } else {
                            $wholedata = $this->getForeachData($row, $data);
                            $wholedata['country_code'] = self::COUNTRY_CODE;
                            $wholedata['region_id'] = self::REGION_ID;
                            $wholedata['weight_from'] = self::WEIGHT_FROM;
                            $wholedata['weight_to'] = self::WEIGHT_TO;
                            $wholedata['numeric_zipcode'] = self::NUMERIC_ZIPCODE;
                            $wholedata['alphanumeric_zipcode'] = self::ALPHANUMERIC_ZIPCODE;
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

    public function addShippingMethodRate($updatedWholedata, $partnerId)
    {
        $updated =0;
        $saved = 0;
        if(!isset($updatedWholedata['zip']) || !isset($updatedWholedata['zip_to']) || !isset($updatedWholedata['price']) || !isset($updatedWholedata['shipping_method'])){
            return false;
        }
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
        }else {
            $shippingModel = $this->_mpshippingModel
                ->create();
            $shippingModel->setData($temp);
            $shippingModel->save();
            ++$saved;
        }
        return [$saved, $updated];
    }

    /**
     * Validate Csv
     *
     * @param $wholedata
     * @return array
     */
    public function validateCsvDataToSave($wholedata)
    {
        $data = [];
        $errors = [];
        foreach ($wholedata as $key => $value) {
            switch ($key) {
                case 'shipping_method':
                    if ($value == '') {
                        $this->messageManager->addError(__('Shipping Method can not be empty'));
                        $this->isError = true;
                    } else {
                        $data[$key] = $value;
                    }
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
                        $this->messageManager->addError(__('Zip field can not be empty'));
                        $this->isError = true;
                    } elseif ((strtolower($wholedata['numeric_zipcode']) == 'yes')
                        && !preg_match('/^([0-9*])+?[0-9.]*$/', $value)) {
                        $this->messageManager->addError(__('Zip field from should be a numeric or * value'));
                        $this->isError = true;
                    } else {
                        $data[$key] = $value;
                    }
                    break;
                case 'zip_to':
                    if ($value == '') {
                        $this->messageManager->addError(__('Zip to field can not be empty'));
                        $this->isError = true;
                    } elseif ((strtolower($wholedata['numeric_zipcode']) == 'yes')
                        && !preg_match('/^([0-9*])+?[0-9.]*$/', $value)) {
                        $this->messageManager->addError(__('Zip to field from should be a numeric or * value'));
                        $this->isError = true;
                    } else {
                        $data[$key] = $value;
                    }
                    break;
                case 'price':
                    if ($value == '') {
                        $this->messageManager->addError(__('Price can not be empty'));
                        $this->isError = true;
                    } elseif (!preg_match('/^([0-9])+?[0-9.]*$/', $value)) {
                        $this->messageManager->addError(__('Not a valid value for price'));
                        $this->isError = true;
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
     * Get Csv File Data
     *
     * @param $rowData          [$rowData description]
     * @param $count            [$count description]
     * @param $headerArray      [$headerArray description]
     */
    public function getCsvFileData($row, $count, $headerArray)
    {
        $flagError = false;
        if (count($row) < 4) {
            $flagError = true;
            $this->messageManager->addError(__('CSV file is not a valid file!'));

        } else {
            $status =($headerArray === $row);
            if (!$status) {
                $flagError = true;
                $this->messageManager->addError(__('Please write the correct header formation of CSV file!'));

            }
        }
        return $flagError;
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
        if ($this->isError === false) {
            list($saved, $updated) = $this->addShippingMethodRate($updatedWholedata, $partnerid);
            $totalSaved +=$saved;
            $totalUpdated +=$updated;
        }
        $this->isError = false;
        return [$totalSaved, $totalUpdated];
    }
}
