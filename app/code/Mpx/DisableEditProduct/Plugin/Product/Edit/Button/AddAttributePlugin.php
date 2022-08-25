<?php

namespace Mpx\DisableEditProduct\Plugin\Product\Edit\Button;

use Magento\Catalog\Block\Adminhtml\Product\Edit\Button\AddAttribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\ScopeInterface;

/**
 * Perform additional authorization for Button Add Attribute  Product  operations.
 */
class AddAttributePlugin
{
    /**
     * @var Http
     */
    protected $request;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Construct
     *
     * @param Http $request
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Http $request,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
    }

    /**
     * Plugin after get button add attribute data
     *
     * @param AddAttribute $subject
     * @param array $result
     * @return array
     */
    public function afterGetButtonData(AddAttribute $subject, array $result): array
    {
        $config     =   $this->scopeConfig
            ->getValue('disableEditProduct/general/enable', ScopeInterface::SCOPE_STORE);
        $controller = $this->request->getControllerName();
        $action     = $this->request->getActionName();
        $route      = $this->request->getRouteName();
        if (!$config) {
            return $result;
        }
        if ($controller === 'product'
            && $action === 'edit'
            && $route === "catalog") {
            $result['disabled'] = true;
            return $result;
        }
        return $result;
    }
}
