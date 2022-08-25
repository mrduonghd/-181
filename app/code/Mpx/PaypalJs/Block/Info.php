<?php


namespace Mpx\PaypalJs\Block;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\Config;

/**
 * class Info
 * display PayPal info order detail
 */
class Info extends \Magento\Payment\Block\Info
{
    /**
     * @var Config
     */
    protected $paymentConfig;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Config $paymentConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Config $paymentConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->paymentConfig = $paymentConfig;
    }

    /**
     * @param $transport
     * @return DataObject|null
     * @throws LocalizedException
     */
    protected function _prepareSpecificInformation($transport = null): ?DataObject
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $data = [];
        $info = $this->getInfo();
        if ($this->_appState
                 ->getAreaCode() === \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
                 && $info->getAdditionalInformation()
        ) {
            foreach ($info->getAdditionalInformation() as $field => $value) {
                //Remove "_" and replace for capitals
                if (!$value) {
                    continue;
                }
                if ($value === 'paypaljs') {
                    $value = 'PayPal';
                }
                if ($value === 'paypalcc') {
                    $value = 'クレジットカード';
                }
                $beautifiedFieldName = str_replace(
                    "_",
                    " ",
                    ucwords(trim(preg_replace('/(?<=\\w)(?=[A-Z])/', " $1", $field)))
                );
                $data[__($beautifiedFieldName)->getText()] = $value;
            }
        }
        return $transport->setData(array_merge($data, $transport->getData()));
    }
}
