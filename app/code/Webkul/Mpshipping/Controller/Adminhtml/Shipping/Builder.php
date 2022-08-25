<?php

/**
 * Mpshipping Admin Shipping Builder Controller
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpshipping\Controller\Adminhtml\Shipping;

use Webkul\Mpshipping\Model\MpshippingFactory;
use Magento\Cms\Model\Wysiwyg as WysiwygModel;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Registry;

class Builder
{
    /**
     * @var \Webkul\Mpshipping\Model\MpshippingFactory
     */
    protected $_shippingFactory;

    /**
     * @param MpshippingFactory $shippingFactory
     */
    public function __construct(
        MpshippingFactory $shippingFactory
    ) {
        $this->_shippingFactory = $shippingFactory;
    }

    /**
     * Build mpshipping based on user request
     *
     * @param RequestInterface $request
     * @return \Webkul\Mpshipping\Model\Mpshipping
     */
    public function build(RequestInterface $request)
    {
        $rowId = (int)$request->getParam('id');
        $shipping = $this->_shippingFactory->create();
        if ($rowId) {
            try {
                $shipping->load($rowId);
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        }
        return $shipping;
    }
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Mpshipping::mpshipping');
    }
}
