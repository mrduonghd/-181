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
namespace Webkul\MpTimeDelivery\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\SessionFactory;
use Webkul\MpTimeDelivery\Block\Options\Option;
use Webkul\MpTimeDelivery\Helper\Data as Helper;

class Configuration extends Template
{
    /**
     * @var Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var Helper
     */
    protected $helper;
    
    /**
     * @param Context $context
     * @param SessionFactory $customerSessionFactory
     * @param Helper $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        SessionFactory $customerSessionFactory,
        Helper $helper,
        array $data = []
    ) {
        $this->customerSessionFactory = $customerSessionFactory;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Prepare global layout.
     *
     * @return $this
     */
    public function _prepareLayout()
    {
        $this->addChild('time_delivery_box', Option::class);

        return parent::_prepareLayout();
    }

    /**
     * return current customer session.
     *
     * @return \Magento\Customer\Model\Session
     */
    public function _getCustomerData()
    {
        return $this->customerSessionFactory->create()->getCustomer();
    }

    /**
     * Get Helper Object
     *
     * @return object
     */
    public function getHelperObject()
    {
        return $this->helper;
    }

    /**
     * Get OptionsBox Html
     *
     * @return string
     */
    public function getOptionsBoxHtml()
    {
        return $this->getChildHtml('time_delivery_box');
    }

    /**
     * Return Form Action Url
     *
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->getUrl('*/account/save', ["_secure" => $this->getRequest()->isSecure()]);
    }
}
