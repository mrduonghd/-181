<?php
/**
 * Webkul Mpshipping Mpshippingset Action Listing Component
 *
 * @category    Webkul
 * @package     Webkul_Mpshipping
 * @author      Webkul Software Private Limited
 *
 */
namespace Webkul\Mpshipping\Ui\Component\Listing\Column\Mpshippingset;

use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Customer\Model\CustomerFactory;

/**
 * Class Sellername for shipping sets
 */
class Sellername extends Column
{

    protected $__shippingmethod;
    /**
     * Constructor
     *
     * @param ContextInterface    $context
     * @param CustomerFactory     $customerFactory
     * @param UiComponentFactory  $uiComponentFactory
     * @param array               $components
     * @param array               $data
     */
    public function __construct(
        ContextInterface $context,
        CustomerFactory $customerFactory,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_customerModel = $customerFactory;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return void
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                if ($item['partner_id']) {
                    $customer = $this->_customerModel->create()
                                      ->load($item['partner_id']);
                    $item[$this->getData('name')] = $customer->getFirstname()." ".$customer->getLastname();
                } else {
                    $item[$this->getData('name')] = "Admin";
                }
            }
        }
        return $dataSource;
    }
}
