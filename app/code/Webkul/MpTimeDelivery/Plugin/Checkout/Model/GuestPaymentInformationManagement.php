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
use Magento\Quote\Model\QuoteFactory;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Webkul\MpTimeDelivery\Helper\Data;

class GuestPaymentInformationManagement
{
    /**
     * @var Magento\Framework\Session\SessionManager
     */
    protected $_coreSession;
    
    /**
     * @var \Magento\Quote\Model\QuoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Quote\Model\QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var \Webkul\MpTimeDelivery\Helper\Data
     */
    protected $_helper;

    /**
     * @param SessionManager $coreSession
     * @param QuoteFactory $quoteFactory
     * @param QuoteIdMaskFactory $quoteIdMaskFactory
     * @param Data $helper
     */
    public function __construct(
        SessionManager $coreSession,
        QuoteFactory $quoteFactory,
        QuoteIdMaskFactory $quoteIdMaskFactory,
        Data $helper
    ) {
        $this->quoteFactory = $quoteFactory;
        $this->_coreSession = $coreSession;
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->_helper = $helper;
    }

    /**
     * @param \Magento\Checkout\Model\GuestPaymentInformationManagement $subject
     * @param int $cartId
     * @param string $email
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface $billingAddress
     * @return array
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Model\GuestPaymentInformationManagement $subject,
        $cartId,
        $email,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        if ($this->_helper->getConfigData('active')) {
            $extAttributes = $paymentMethod->getExtensionAttributes();
            if ($extAttributes->getSellerData() === null) {
                return [$cartId, $email, $paymentMethod, $billingAddress];
            }

            $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
            $quote = $this->quoteFactory->create()->load($quoteIdMask->getQuoteId());
            
            $sellerData = $this->_helper->getJson()->unserialize($extAttributes->getSellerData());
            $sellerId = 0;
            foreach ($quote->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }
                $mpassignproductId = $this->_helper->getAssignProduct($item);
                $sellerId = $this->_helper->getSellerId($mpassignproductId, $item->getProductId());
                if (is_array($sellerData)) {
                    foreach ($sellerData as $value) {
                        if ($sellerId == $value['id']) {
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
        return [$cartId, $email, $paymentMethod, $billingAddress];
    }
}
