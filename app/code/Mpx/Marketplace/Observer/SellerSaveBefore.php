<?php
/**
 * Mpx Software.
 *
 * @category  Mpx
 * @package   Mpx_Marketplace
 * @author    Mpx
 */

namespace Mpx\Marketplace\Observer;

/**
 * Mpx Marketplace CountryPic Observer.
 */
class SellerSaveBefore implements \Magento\Framework\Event\ObserverInterface
{
    const COUNTRY_PIC = 'JP';

    /**
     * Set the default country handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $observer->getObject()->setData('country_pic', self::COUNTRY_PIC);
        return $this;
    }
}
