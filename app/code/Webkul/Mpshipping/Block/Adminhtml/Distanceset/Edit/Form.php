<?php

/**
 * Webkul Mpshipping Distanceset Edit Form Block
 *
 * @category    Webkul
 * @package     Webkul_Mpshipping
 * @author      Webkul Software Private Limited
 *
 */

namespace Webkul\Mpshipping\Block\Adminhtml\Distanceset\Edit;

/**
 * Adminhtml permissions warehouse edit form
 *
 */
/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @return $this
     */
    public function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                            'id' => 'edit_form',
                            'action' => $this->getData('action'),
                            'method' => 'post']
                        ]
        );
        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
}
