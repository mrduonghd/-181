<?php

/**
 * Webkul Mpshipping Distanceset Edit Tab Main Block
 *
 * @category    Webkul
 * @package     Webkul_Mpshipping
 * @author      Webkul Software Private Limited
 *
 */

namespace Webkul\Mpshipping\Block\Adminhtml\Distanceset\Edit\Tab;

/**
 * Cms page edit form main tab
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
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
        \Webkul\Mpshipping\Helper\Data $mpshippingHelper,
        \Webkul\Mpshipping\Model\Mpshippingmethod $shippingmethod,
        \Magento\Framework\Escaper $escaper,
        array $data = []
    ) {
        $this->_mpshippingHelper = $mpshippingHelper;
        $this->shippingmethod = $shippingmethod;
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
        $distancesetModel = $this->_coreRegistry->registry('mpshippingDist_shipping');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();

        $baseFieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Shipping Set Information')]
        );

        if ($distancesetModel->getEntityId()) {
            $baseFieldset->addField(
                'entity_id',
                'hidden',
                ['name' => 'entity_id']
            );
        }

        $baseFieldset->addField(
            'price_from',
            'text',
            [
                'name' => 'price_from',
                'label' => __('Price From'),
                'id' => 'price_from',
                'title' => __('Price From'),
                'class' => 'required-entry validate-not-negative-number',
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'price_to',
            'text',
            [
                'name' => 'price_to',
                'label' => __('Price To'),
                'id' => 'price_to',
                'title' => __('Price To'),
                'class' => 'required-entry validate-not-negative-number',
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'dist_from',
            'text',
            [
                'name' => 'dist_from',
                'label' => __('Distance From'),
                'id' => 'dist_from',
                'title' => __('Distance From'),
                'class' => 'required-entry validate-not-negative-number',
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'dist_to',
            'text',
            [
                'name' => 'dist_to',
                'label' => __('Distance To'),
                'id' => 'dist_to',
                'title' => __('Distance To'),
                'class' => 'required-entry validate-not-negative-number',
                'required' => true
            ]
        );

        $baseFieldset->addField(
            'method_name',
            'text',
            [
                'name' => 'method_name',
                'label' => __('Method Name'),
                'id' => 'method_name',
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

        $data= $distancesetModel->getData();
        if (isset($data['shipping_method_id'])) {
            $methodName = $this->shippingmethod->load($data['shipping_method_id'])->getMethodName();
            $data['method_name'] = $this->escaper->escapeHtml($methodName);
        }

        $form->setValues($data);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}
