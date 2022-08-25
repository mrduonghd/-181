<?php

namespace Mpx\Sales\Plugin\Order;

use Magento\Framework\App\Request\Http;
use Magento\Framework\DataObject;
use Magento\Sales\Block\Order\Totals;

/**
 * Update param $after
 * Class BeforeTotals
 */
class BeforeTotals
{

    /**
     * @var Http
     */
    protected $request;

    /**
     * Construct
     *
     * @param Http $request
     */
    public function __construct(
        Http $request
    ) {
        $this->request = $request;
    }

    /**
     * Update param $after
     *
     * @param Totals $subject
     * @param DataObject $total
     * @param string|null $after
     * @return string[]
     */
    public function beforeAddTotal(Totals $subject, DataObject $total, string $after = null): array
    {
        $controller = $this->request->getControllerName();
        $action     = $this->request->getActionName();
        $route      = $this->request->getRouteName();
        if ($controller === "order" && $action === "view" && $route === "sales") {
            $after = 'grand_total';
            return [$total,$after];
        }
        return [$total,$after];
    }
}
