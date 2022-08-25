<?php
/**
 * Mpx Software.
 *
 * @category  Mpx
 * @package   Mpx_Marketplace
 * @author    Mpx
 */

namespace Mpx\Marketplace\Model\Order\Pdf;

use Magento\Customer\Model\Session;

/**
 * Marketplace Order Shipment PDF model
 */
class Shipment extends \Webkul\Marketplace\Model\Order\Pdf\Shipment
{
    /**
     * Return PDF document
     *
     * @param \Magento\Sales\Model\Order\Shipment[] $shipments
     * @return \Zend_Pdf
     */
    public function getPdf($shipments = [])
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('shipment');

        $pdf = new \Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new \Zend_Pdf_Style();
        $this->_setFontBold($style, 10);
        foreach ($shipments as $shipment) {
            if ($shipment->getStoreId()) {
                $this->_localeResolver->emulate($shipment->getStoreId());
                $this->_storeManager->setCurrentStore($shipment->getStoreId());
            }
            $page = $this->newPage();
            $order = $shipment->getOrder();
            /* Add image */
            $this->insertLogo($page, $shipment->getStore());
            /* Add address */
            $this->insertAddress($page, $shipment->getStore());
            /* Add head */
            $this->insertOrder(
                $page,
                $shipment,
                $this->_scopeConfig->isSetFlag(
                    self::XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $order->getStoreId()
                )
            );
            /* Add document text and number */
            $this->insertDocumentNumber($page,__(''));
            /* Add table */
            $this->_drawHeader($page);
            /* Add body */
            foreach ($shipment->getAllItems() as $item) {
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }
                /* Draw item */
                $this->_drawItem($item, $page, $order);
                $page = end($pdf->pages);
            }
            if ($shipment->getStoreId()) {
                $this->_localeResolver->revert();
            }
        }
        $this->_afterGetPdf();
        return $pdf;
    }

    /**
     * Insert order to seller's order pdf page
     *
     * @param \Zend_Pdf_Page &$sellerPdfPage
     * @param \Magento\Sales\Model\Order $sellerOrderObj
     * @param bool $putOrderId
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function insertOrder(&$sellerPdfPage, $sellerOrderObj, $putOrderId = true)
    {
        if ($sellerOrderObj instanceof \Magento\Sales\Model\Order) {
            $sellerShipment = null;
            $sellerOrder = $sellerOrderObj;
        } elseif ($sellerOrderObj instanceof \Magento\Sales\Model\Order\Shipment) {
            $sellerShipment = $sellerOrderObj;
            $sellerOrder = $sellerShipment->getOrder();
        }

        $this->y = $this->y ? $this->y : 815;
        $top = $this->y;

        $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $sellerPdfPage->setLineColor(new \Zend_Pdf_Color_GrayScale(0.45));
        $sellerPdfPage->drawRectangle(25, $top, 570, $top - 55);
        $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $this->setDocHeaderCoordinates([25, $top, 570, $top - 55]);
        $this->_setFontRegular($sellerPdfPage, 10);

        if ($putOrderId) {
            $sellerPdfPage->drawText(
                __('Order # ') . $sellerOrder->getRealOrderId(),
                35,
                $top -= 18,
                'UTF-8'
            );
        }
        $sellerPdfPage->drawText(
            __('Order Date: ') .
            $this->_localeDate->formatDate(
                $this->_localeDate->scopeDate(
                    $sellerOrder->getStore(),
                    $sellerOrder->getCreatedAt(),
                    true
                ),
                \IntlDateFormatter::MEDIUM,
                false
            ),
            35,
            $top -= 15,
            'UTF-8'
        );

        $top -= 10;
        $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $sellerPdfPage->setLineColor(new \Zend_Pdf_Color_GrayScale(0.5));
        $sellerPdfPage->setLineWidth(0.5);
        $sellerPdfPage->drawRectangle(25, $top, 275, $top - 25);
        $sellerPdfPage->drawRectangle(275, $top, 570, $top - 25);

        if ($this->helper->getSellerProfileDisplayFlag()) {
            /* Calculate blocks info */
            $this->doInsertOrderExecution($sellerPdfPage, $sellerOrder, $sellerShipment, $top);
        } else {
            /* Calculate blocks info */

            /* Billing Address */
            $billingAddress = $this->_formatAddress(
                $this->addressRenderer->format(
                    $sellerOrder->getBillingAddress(),
                    'pdf'
                )
            );

            /* Shipping Address and Method */
            if (!$sellerOrder->getIsVirtual()) {
                /* Shipping Address */
                $shippingAddress = $this->_formatAddress(
                    $this->addressRenderer->format($sellerOrder->getShippingAddress(), 'pdf')
                );
                $shippingMethod = $sellerOrder->getShippingDescription();
            }

            $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $this->_setFontBold($sellerPdfPage, 12);
            $sellerPdfPage->drawText(__('Payment Method:'), 35, $top - 15, 'UTF-8');
            if (!$sellerOrder->getIsVirtual()) {
                $sellerPdfPage->drawText(__('Shipping Method:'), 285, $top - 15, 'UTF-8');
            }

            $addressesHeight = $this->_calcAddressHeight($billingAddress);
            if (isset($shippingAddress)) {
                $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
            }

            $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
            if (!$sellerOrder->getIsVirtual()) {
                $tracks = [];
                if ($sellerShipment) {
                    $tracks = $sellerShipment->getAllTracks();
                }
                if (count($tracks)) {
                    $addressesHeight = 30 * count($tracks) + $addressesHeight;
                }
                $sellerPdfPage->drawRectangle(25, $top - 25, 570, $top - 33 - $addressesHeight);
            } else {
                $sellerPdfPage->drawRectangle(25, $top - 25, 570, $top - 65);
            }
            $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $this->_setFontRegular($sellerPdfPage, 10);
            $this->y = $top - 40;
            $this->y -= 15;

            if (!$sellerOrder->getIsVirtual()) {
                $topMargin = 15;
                $methodStartY = $this->y;

                foreach ($this->string->split($shippingMethod, 45, true, true) as $_value) {
                    $sellerPdfPage->drawText(strip_tags(trim($_value)), 285, $this->y, 'UTF-8');
                    $this->y -= 15;
                }

                $yShipments = $this->y;
                $totalShippingChargesText = "(" . __(
                        'Total Shipping Charges'
                    ) . " " . $sellerOrder->formatPriceTxt(
                        $sellerOrder->getShippingAmount()
                    ) . ")";

                $sellerPdfPage->drawText(
                    $totalShippingChargesText,
                    285,
                    $yShipments - $topMargin,
                    'UTF-8'
                );
                $yShipments -= $topMargin + 10;

                $tracks = [];
                if ($sellerShipment) {
                    $tracks = $sellerShipment->getAllTracks();
                }
                if (count($tracks)) {
                    $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                    $sellerPdfPage->setLineWidth(0.5);
                    $sellerPdfPage->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                    $sellerPdfPage->drawLine(400, $yShipments, 400, $yShipments - 10);

                    $this->_setFontRegular($sellerPdfPage, 9);
                    $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                    $sellerPdfPage->drawText(__('Title'), 290, $yShipments - 7, 'UTF-8');
                    $sellerPdfPage->drawText(__('Number'), 410, $yShipments - 7, 'UTF-8');

                    $yShipments -= 20;
                    $this->_setFontRegular($sellerPdfPage, 8);
                    foreach ($tracks as $track) {
                        $maxTitleLen = 45;
                        $endOfTitle = strlen($track->getTitle()) > $maxTitleLen ? '...' : '';
                        $truncatedTitle = substr($track->getTitle(), 0, $maxTitleLen) . $endOfTitle;
                        $sellerPdfPage->drawText($truncatedTitle, 292, $yShipments, 'UTF-8');
                        $sellerPdfPage->drawText($track->getNumber(), 410, $yShipments, 'UTF-8');
                        $yShipments -= $topMargin - 5;
                    }
                } else {
                    $yShipments -= $topMargin - 5;
                }

                $currentY = min($this->y, $yShipments);

                $this->y = $currentY;
                $this->y -= 15;
            } else {
                $this->y -= 55;
            }
        }
    }

    protected function doInsertOrderExecution($sellerPdfPage, $sellerOrder, $sellerShipment, $top)
    {
        /* Billing Address */
        $billingAddress = $this->_formatAddress(
            $this->addressRenderer->format(
                $sellerOrder->getBillingAddress(),
                'pdf'
            )
        );

        /* Shipping Address and Method */
        if (!$sellerOrder->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress(
                $this->addressRenderer->format($sellerOrder->getShippingAddress(), 'pdf')
            );
            $shippingMethod = $sellerOrder->getShippingDescription();
        }

        $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontBold($sellerPdfPage, 12);
        $sellerPdfPage->drawText(__('Sold to:'), 35, $top - 15, 'UTF-8');

        if (!$sellerOrder->getIsVirtual()) {
            $sellerPdfPage->drawText(__('Ship to:'), 285, $top - 15, 'UTF-8');
        } else {
            $sellerPdfPage->drawText(__('Payment Method:'), 285, $top - 15, 'UTF-8');
        }

        $addressesHeight = $this->_calcAddressHeight($billingAddress);
        if (isset($shippingAddress)) {
            $addressesHeight = max($addressesHeight, $this->_calcAddressHeight($shippingAddress));
        }

        $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(1));
        $sellerPdfPage->drawRectangle(25, $top - 25, 570, $top - 33 - $addressesHeight);
        $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($sellerPdfPage, 10);
        $this->y = $top - 40;
        $addressesStartY = $this->y;

        foreach ($billingAddress as $value) {
            $sellerPdfPage = $this->calculateBillingYaxis($value, $sellerPdfPage);
        }

        $addressesEndY = $this->y;

        if (!$sellerOrder->getIsVirtual()) {
            $this->y = $addressesStartY;
            foreach ($shippingAddress as $value) {
                $sellerPdfPage = $this->calculateShippingYaxis($value, $sellerPdfPage);
            }

            $addressesEndY = min($addressesEndY, $this->y);
            $this->y = $addressesEndY;

            $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $sellerPdfPage->setLineWidth(0.5);
            $sellerPdfPage->drawRectangle(25, $this->y, 275, $this->y - 25);
            $sellerPdfPage->drawRectangle(275, $this->y, 570, $this->y - 25);

            $this->y -= 15;
            $this->_setFontBold($sellerPdfPage, 12);
            $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
            $sellerPdfPage->drawText(__('Payment Method'), 35, $this->y, 'UTF-8');
            $sellerPdfPage->drawText(__('Shipping Method:'), 285, $this->y, 'UTF-8');

            $this->y -= 10;
            $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($sellerPdfPage, 10);
            $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = 35;
            $yPayments = $this->y - 15;
        } else {
            $yPayments = $addressesStartY;
            $paymentLeft = 285;
        }

        if ($sellerOrder->getIsVirtual()) {
            // replacement of Shipments-Payments rectangle block
            $yPayments = min($addressesEndY, $yPayments);
            $sellerPdfPage->drawLine(25, $top - 25, 25, $yPayments);
            $sellerPdfPage->drawLine(570, $top - 25, 570, $yPayments);
            $sellerPdfPage->drawLine(25, $yPayments, 570, $yPayments);

            $this->y = $yPayments - 15;
        } else {
            $topMargin = 15;
            $methodStartY = $this->y;
            $this->y -= 15;

            foreach ($this->string->split($shippingMethod, 45, true, true) as $_value) {
                $sellerPdfPage->drawText(strip_tags(trim($_value)), 285, $this->y, 'UTF-8');
                $this->y -= 15;
            }

            $yShipments = $this->y;
            $totalShippingChargesText = "(" . __(
                    'Total Shipping Charges'
                ) . " " . $sellerOrder->formatPriceTxt(
                    $sellerOrder->getShippingAmount()
                ) . ")";

            $sellerPdfPage->drawText($totalShippingChargesText, 285, $yShipments - $topMargin, 'UTF-8');
            $yShipments -= $topMargin + 10;

            $tracks = [];
            if ($sellerShipment) {
                $tracks = $sellerShipment->getAllTracks();
            }
            if (count($tracks)) {
                $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $sellerPdfPage->setLineWidth(0.5);
                $sellerPdfPage->drawRectangle(285, $yShipments, 510, $yShipments - 10);
                $sellerPdfPage->drawLine(400, $yShipments, 400, $yShipments - 10);

                $this->_setFontRegular($sellerPdfPage, 9);
                $sellerPdfPage->setFillColor(new \Zend_Pdf_Color_GrayScale(0));
                $sellerPdfPage->drawText(__('Delivery company'), 290, $yShipments - 7, 'UTF-8');
                $sellerPdfPage->drawText(__('Number'), 410, $yShipments - 7, 'UTF-8');

                $yShipments -= 20;
                $this->_setFontRegular($sellerPdfPage, 8);
                foreach ($tracks as $track) {
                    $maxTitleLen = 45;
                    $endOfTitle = strlen($track->getTitle()) > $maxTitleLen ? '...' : '';
                    $truncatedTitle = substr($track->getTitle(), 0, $maxTitleLen) . $endOfTitle;
                    $sellerPdfPage->drawText($truncatedTitle, 292, $yShipments, 'UTF-8');
                    $sellerPdfPage->drawText($track->getNumber(), 410, $yShipments, 'UTF-8');
                    $yShipments -= $topMargin - 5;
                }
                $this->_setFontRegular($sellerPdfPage, 10);
            } else {
                $yShipments -= $topMargin - 5;
            }

            $currentY = min($yPayments, $yShipments);

            // replacement of Shipments-Payments rectangle block
            $sellerPdfPage->drawLine(25, $methodStartY, 25, $currentY);
            //left
            $sellerPdfPage->drawLine(25, $currentY, 570, $currentY);
            //bottom
            $sellerPdfPage->drawLine(570, $currentY, 570, $methodStartY);
            //right

            $this->y = $currentY;
            $this->y -= 15;
        }
    }

}
