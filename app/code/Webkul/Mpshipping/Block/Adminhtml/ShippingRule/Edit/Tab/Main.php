<?php
/**
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpshipping\Block\Adminhtml\ShippingRule\Edit\Tab;

/**
 * Cms page edit form main tab
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Webkul\Mpshipping\Model\Shippingtype
     */
    private $shippingtypeList;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Webkul\Mpshipping\Model\Shippingtype $shippingtypeList,
        \Webkul\Mpshipping\Helper\Data $mpshippingHelper,
        \Webkul\Mpshipping\Model\Mpshippingmethod $shippingmethod,
        \Magento\Directory\Model\Config\Source\Country $country,
        \Webkul\Mpshipping\Model\Config\Source\Yesno $yesno,
        \Magento\Framework\Escaper $escaper,
        array $data = []
    ) {
        $this->_mpshippingHelper = $mpshippingHelper;
        $this->shippingtypeList = $shippingtypeList;
        $this->shippingmethod = $shippingmethod;
        $this->country = $country;
        $this->yesno = $yesno;
        $this->escaper = $escaper;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form fields
     *
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     * @return \Magento\Backend\Block\Widget\Form
     */
    public function _prepareForm()
    {
        $helper = $this->_mpshippingHelper;
        $sellerList = $helper->getSellerList();
        $shippingModel = $this->_coreRegistry->registry('mpshippingrule_shipping');
        $countries = $this->country->toOptionArray(false, 'US');
        $yesnoOptions = $this->yesno->toOptionArray();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $baseFieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Shipping Rule Information')]
        );
        $data= $shippingModel->getData();
        $zipcodeRequiredStatus = ($data['is_range'] == 'no') ? true : false;
        $zipcodeClass = ($data['is_range'] == 'no') ? 'required-entry' : '';
        if ($shippingModel->getMpshippingId()) {
            $baseFieldset->addField(
                'mpshipping_id',
                'hidden',
                ['name' => 'mpshipping_id']
            );
        }

        $baseFieldset->addField(
            'dest_country_id',
            'select',
            [
              'name' => 'dest_country_id',
              'label' => __('Country code'),
              'required' => true,
              'class' => 'required-entry',
              'values' => $countries
             ]
        );

        $baseFieldset->addField(
            'dest_region_id',
            'text',
            [
                'name' => 'dest_region_id',
                'label' => __('Region code'),
                'id' => 'dest_region_id',
                'title' => __('Region code'),
                'class' => 'required-entry',
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'weight_from',
            'text',
            [
                'name' => 'weight_from',
                'label' => __('Weight from'),
                'id' => 'weight_from',
                'title' => __('Weight from'),
                'class' => 'required-entry validate-number validate-zero-or-greater',
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'weight_to',
            'text',
            [
                'name' => 'weight_to',
                'label' => __('Weight To'),
                'id' => 'weight_to',
                'title' => __('Weight To'),
                'class' => 'required-entry validate-not-negative-number',
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'dest_zip',
            'text',
            [
                'name' => 'dest_zip',
                'label' => __('Zip From'),
                'id' => 'dest_zip',
                'title' => __('Zip From'),
                'class' => 'required-entry',
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'dest_zip_to',
            'text',
            [
                'name' => 'dest_zip_to',
                'label' => __('Zip To'),
                'id' => 'dest_zip_to',
                'title' => __('Zip To'),
                'class' => 'required-entry',
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'shipping_method',
            'text',
            [
                'name' => 'shipping_method',
                'label' => __('Method Name'),
                'id' => 'shipping_method',
                'title' => __('Method Name'),
                'class' => 'required-entry',
                'required' => true
            ]
        );
        $baseFieldset->addField(
            'partner_id',
            'select',
            [
                'name' => 'partner_id',
                'label' => __('Seller'),
                'id' => 'partner_id',
                'title' => __('Seller'),
                'values' => $sellerList,
                'class' => 'required-entry',
                'required' => true
            ]
        );
        $baseFieldset->addField(
            'is_range',
            'select',
            [
                'name' => 'is_range',
                'label' => __('Numeric Zipcode'),
                'id' => 'is_range',
                'title' => __('Numeric Zipcode'),
                'values' => $yesnoOptions,
                'class' => 'required-entry',
                'required' => true
            ]
        );
        $baseFieldset->addField(
            'zipcode',
            'text',
            [
                'name' => 'zipcode',
                'label' => __('Alphanumeric Zipcode'),
                'id' => 'zipcode',
                'title' => __('Alphanumeric Zipcode'),
                'class' => $zipcodeClass,
                'required' => $zipcodeRequiredStatus
            ]
        );
        $baseFieldset->addField(
            'price',
            'text',
            [
                'name' => 'price',
                'label' => __('Price'),
                'id' => 'wk_price',
                'title' => __('Shipping Price'),
                'class' => 'required-entry validate-not-negative-number',
                'style' => 'border-width: 1px',
                'required' => true
            ]
        );
        if (isset($data['shipping_method_id'])) {
            $methodName = $this->shippingmethod->load($data['shipping_method_id'])->getMethodName();
            $data['shipping_method'] = $this->escaper->escapeHtml($methodName);
        }

        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
