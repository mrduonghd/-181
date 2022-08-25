<?php
namespace Mpx\Marketplace\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * @var Disable
 * disable filed Global Commission Rate
 */
class Disable extends Field
{

    /**
     * 'disable filed'
     *
     * @param AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element): string
    {
        $element->setDisabled('disabled');
        return $element->getElementHtml();
    }
}
