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
namespace Webkul\MpTimeDelivery\Block\Link;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\DefaultPathInterface;
use Webkul\Marketplace\Helper\Data;

class Current extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $helper;
 
    /**
     * Constructor
     *
     * @param Context               $context
     * @param DefaultPathInterface  $defaultPath
     * @param Data                  $marketplaceHelper
     * @param array                 $data
     */
    public function __construct(
        Context $context,
        DefaultPathInterface $defaultPath,
        Data $marketplaceHelper,
        array $data = []
    ) {
        $this->helper = $marketplaceHelper;
        parent::__construct($context, $defaultPath, $data);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $isPartner= $this->helper->isSeller();
        $html = '';
        if ($isPartner) {
            return parent::_toHtml();
        }
        return $html;
    }
}
