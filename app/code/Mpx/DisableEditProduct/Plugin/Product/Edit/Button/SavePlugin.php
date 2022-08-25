<?php

namespace Mpx\DisableEditProduct\Plugin\Product\Edit\Button;

use Magento\ConfigurableProduct\Block\Adminhtml\Product\Edit\Button\Save;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Store\Model\ScopeInterface;

/**
 * Perform additional authorization for Button Save  Product  operations.
 */
class SavePlugin
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
     * Plugin after get button save data
     *
     * @param Save $subject
     * @param array $result
     * @return array
     */
    public function afterGetButtonData(Save $subject, array $result): array
    {
        $config  = $this->scopeConfig
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
