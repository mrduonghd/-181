<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpTimeDeliverys
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpTimeDelivery\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Slot Orderlink.
 */
class Orderlink extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor.
     *
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['order_id'])) {
                    $item[$fieldName] = "<a href='".
                        $this->urlBuilder->getUrl(
                            'sales/order/view',
                            ['order_id' => $item['order_id']]
                        )."' target='blank' title='".__('View Order')."'>".$item['order_id'].'</a>';
                }
            }
        }
        return $dataSource;
    }
}
