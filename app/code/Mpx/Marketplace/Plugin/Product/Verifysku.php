<?php

namespace Mpx\Marketplace\Plugin\Product;

class Verifysku
{
    const UNICODE_HYPHEN_MINUS = "\u{002D}";

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->customerSession = $customerSession;
    }

    /**
     * Set value sku format before check sku exits
     *
     * @param \Webkul\Marketplace\Controller\Product\Verifysku $subject
     * @return void
     */
    public function beforeExecute(\Webkul\Marketplace\Controller\Product\Verifysku $subject)
    {
        $params = $subject->getRequest()->getParams();
        $skuFormat = $this->formatSku($params['sku']);
        $params['sku'] = $skuFormat;
        $subject->getRequest()->setParams($params);
    }

    /**
     * Format Sku
     *
     * @param string $sku
     * @return string
     */
    public function formatSku($sku)
    {
        $sellerId = $this->customerSession->getCustomer()->getId();
        $skuPrefix = str_pad($sellerId, 3, "0", STR_PAD_LEFT);

        return $skuPrefix.self::UNICODE_HYPHEN_MINUS.$sku;
    }
}
