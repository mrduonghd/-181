<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpTimeDelivery
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpTimeDelivery\Plugin\Checkout\Model;

use Magento\Framework\Session\SessionManager;
use Magento\Framework\Json\Helper\Data as JsonHelper;

class ShippingInformationManagement
{
    /**
     * @var \Magento\Framework\Session\SessionManager
     */
    protected $_coreSession;
    /**
     * @var \Magento\Quote\Model\QuoteRepository
     */
    protected $quoteRepository;

    /**
     * @var \Webkul\MpTimeDelivery\Helper\Data
     */
    protected $_helper;

    /**
     * @var Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param SessionManager                            $coreSession
     * @param \Magento\Quote\Model\QuoteRepository      $quoteRepository
     * @param \Webkul\MpTimeDelivery\Helper\Data        $helper
     * @param JsonHelper                                $jsonHelper
     */
    public function __construct(
        SessionManager $coreSession,
        \Magento\Quote\Model\QuoteRepository $quoteRepository,
        \Webkul\MpTimeDelivery\Helper\Data $helper,
        JsonHelper $jsonHelper
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->_coreSession = $coreSession;
        $this->_helper = $helper;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * @param \Magento\Checkout\Model\ShippingInformationManagement   $subject
     * @param $cartId
     * @param \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
     * return $array
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        \Magento\Checkout\Api\Data\ShippingInformationInterface $addressInformation
    ) {
        if ($this->_helper->getConfigData('active')) {
            $extAttributes = $addressInformation->getExtensionAttributes();
            if ($extAttributes->getSellerData() === null) {
                return [$cartId, $addressInformation];
            }
            $sellerData = $this->jsonHelper->jsonDecode($extAttributes->getSellerData());

            $quote = $this->quoteRepository->getActive($cartId);
            $sellerId = 0;
            foreach ($quote->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }
                $mpassignproductId = $this->_helper->getAssignProduct($item);
                $sellerId = $this->_helper->getSellerId($mpassignproductId, $item->getProductId());
                if (is_array($sellerData)) {
                    foreach ($sellerData as $value) {
                        if ($value['id'] == 0) {
                            $item->setDeliveryDate($value['date']);
                            $item->setDeliveryTime($value['slot_time']);
                        }elseif ($sellerId == $value['id']) {
                            $item->setDeliveryDate($value['date']);
                            $item->setDeliveryTime($value['slot_time']);
                        }
                    }
                }
            }
            if ($this->_coreSession->getSellerSlotInfo()) {
                $this->_coreSession->unsSellerSlotInfo();
                $this->_coreSession->setSellerSlotInfo($sellerData);
            } else {
                $this->_coreSession->setSellerSlotInfo($sellerData);
            }
        }
        return [$cartId, $addressInformation];
    }
}
