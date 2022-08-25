<?php

/**
 * Webkul Mpshipping Shipping Edit Tab Admin Block
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpshipping\Block\Adminhtml\Shipping\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Backend\Model\Auth\Session;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * @var InlineInterface
     */
    protected $_translateInline;

    /**
     * @param Context                                   $context
     * @param InlineInterface                           $translateInline
     * @param EncoderInterface                          $jsonEncoder
     * @param Session                                   $authSession
     * @param array                                     $data
     */

    public function __construct(
        Context $context,
        InlineInterface $translateInline,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        array $data = []
    ) {
        $this->_translateInline = $translateInline;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('shipping_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Shipping Information'));
    }

    /**
     * Prepare Layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addTab(
            'shippinginfo',
            [
                'label' => __('Shipping Detail'),
                'url' => $this->getUrl('mpshipping/*/grid', ['_current' => true]),
                'class' => 'ajax'
            ]
        );
        $this->addTab(
            'addshipping',
            [
                'label' => __('Add Shipping'),
                'content'=>$this->getLayout()->createBlock(
                    \Webkul\Mpshipping\Block\Adminhtml\Shipping\Edit\Tab\Form::class
                )->toHtml(),
            ]
        );
        return parent::_prepareLayout();
    }

    /**
     * Translate html content
     * @param string $html
     * @return string
     */
    protected function _translateHtml($html)
    {
        $this->_translateInline->processResponseBody($html);
        return $html;
    }
}
