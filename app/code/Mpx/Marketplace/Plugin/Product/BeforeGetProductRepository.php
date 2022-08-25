<?php

namespace Mpx\Marketplace\Plugin\Product;

use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\Request\Http;

/**
 * Change param $forceReload get function to true
 *
 * Class BeforeGetProductRepository
 */
class BeforeGetProductRepository
{
    /**
     * @var Http
     */
    protected $request;

    /**
     * @param Http $request
     */
    public function __construct(
        Http $request
    )
    {
        $this->request = $request;
    }

    /**
     * Change input of get . function
     *
     * @param ProductRepository $subject
     * @param $sku
     * @param $editMode
     * @param $storeId
     * @param $forceReload
     * @return array
     */
    public function beforeGet(ProductRepository $subject, $sku, $editMode = false, $storeId = null, $forceReload = false): array
    {
        $moduleName = $this->request->getModuleName();
        $controller = $this->request->getControllerName();
        $action = $this->request->getActionName();
        if ($moduleName === "marketplace" && $controller === "product" && $action === "save") {
            return [$sku, $editMode, $storeId, true];
        }
        return [$sku, $editMode, $storeId, false];
    }
}
