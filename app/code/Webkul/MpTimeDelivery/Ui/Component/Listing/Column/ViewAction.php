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

namespace Webkul\MpTimeDelivery\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Slot ViewAction.
 */
class ViewAction extends Column
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     *
     * @param ContextInterface                                   $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\UrlInterface                    $urlBuilder
     * @param array                                              $components
     * @param array                                              $data
     */
    public function __construct(
        ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Framework\UrlInterface $urlBuilder,
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
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['entity_id'])) {
                    $viewUrl = $this->getData('config/viewUrlPath') ?: '#';
                    $urlEntityParam = $this->getData('config/urlEntityParamName') ?: 'slot_id';
                    $item[$this->getData('name')] = [
                        'view' => [
                            'href' => $this->urlBuilder->getUrl(
                                $viewUrl,
                                [
                                    $urlEntityParam => $item['entity_id'],
                                ]
                            ),
                            'label' => __('Order'),
                        ],
                    ];
                }
            }
        }

        return $dataSource;
    }
}
