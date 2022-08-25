<?php

namespace Mpx\Marketplace\Block\Adminhtml\Customer\Edit;

/**
 * Customer account form block.
 */
class AddSellerTab extends \Webkul\Marketplace\Block\Adminhtml\Customer\Edit\AddSellerTab
{

    /**
     * Add seller form admin
     *
     * @return $this|AddSellerTab
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function initForm(): AddSellerTab
    {
        if (!$this->canShowTab()) {
            return $this;
        }
        /**@var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('marketplace_');
        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Do You Want To Make This Customer As Seller ?')]
        );

        $coll = $this->customerEdit->getMarketplaceSellerCollection();
        $profileurl = '';
        $profiletitle ='';
        foreach ($coll as $row) {
            $profileurl = $row->getShopUrl();
            $profiletitle = $row->getShopTitle();
        }

        $fieldset->addField(
            'profileurl',
            'text',
            [
                'name' => 'profileurl',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Shop Url'),
                'title' => __('Shop Url'),
                'value' => $profileurl,
            ]
        );

        $fieldset->addField(
            'profiletitle',
            'text',
            [
                'name' => 'profiletitle',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Shop Title'),
                'title' => __('Shop Title'),
                'class'     => 'market_profiletitle validate-shop-title',
                'value' => $profiletitle,
            ]
        );

        $fieldset->addField(
            'is_seller_add',
            'checkbox',
            [
                'name' => 'is_seller_add',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Approve Seller'),
                'title' => __('Approve Seller'),
                'onchange' => 'this.value = this.checked;',
            ]
        );
        $this->setForm($form);

        return $this;
    }
}
