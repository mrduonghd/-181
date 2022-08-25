<?php
/**
 * Mpx Software.
 *
 * @category  Mpx
 * @package   Mpx_Marketplace
 * @author    Mpx
 */

namespace Mpx\Marketplace\Plugin\Customer;

use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Fieldset;
use Webkul\Marketplace\Block\Adminhtml\Customer\Edit\Tabs;

/**
 * Mpx Marketplace remove vat customer.
 */
class RemoveVatCustomer
{
    /**
     * Function to run to remove vat.
     *
     * @param Tabs $subject
     * @param Form $form
     * @return Form[]
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSetForm(
        Tabs $subject,
        Form  $form
    ) {
        /** @var Fieldset $fieldset */

        $fieldset = $form->getElement('base_fieldset');
        $fieldset->getElements()->remove('taxvat');

        return [$form];
    }
}
