<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_MpTimeDelivery
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\MpTimeDelivery\Block;

use Magento\Framework\View\Element\Template;

class DefaultOrderNew extends \Magento\Sales\Block\Order\Email\Items\Order\DefaultOrder
{

     /**
      * Get config
      *
      * @param  string $path
      * @return string|null
      */
    public function getConfig($path)
    {
        return $this->_scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
