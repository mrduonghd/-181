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
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class Price
 */
class Time extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * Constructor
     *
     * @param ContextInterface      $context
     * @param UiComponentFactory    $uiComponentFactory
     * @param DateTime              $dateTime,
     * @param array                 $components
     * @param array                 $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        DateTime $dateTime,
        array $components = [],
        array $data = []
    ) {
        $this->dateTime = $dateTime;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param  array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$this->getData('name')] = $this->dateTime->gmtDate('h:i A', $item[$this->getData('name')]);
            }
        }

        return $dataSource;
    }
}
