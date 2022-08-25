<?php

declare(strict_types=1);

namespace Mpx\DisableEditProduct\Ui\Component\Product;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Disable MassAction List Product
 */
class MassAction extends \Magento\Catalog\Ui\Component\Product\MassAction
{
    public const NAME = 'massaction';

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param AuthorizationInterface $authorization
     * @param ContextInterface $context
     * @param ScopeConfigInterface $scopeConfig
     * @param array $components
     * @param array $data
     */
    public function __construct(
        AuthorizationInterface $authorization,
        ContextInterface $context,
        ScopeConfigInterface $scopeConfig,
        array $components = [],
        array $data = []
    ) {
        $this->authorization = $authorization;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($authorization, $context, $components, $data);
    }

    /**
     * @inheritdoc
     */
    public function prepare() : void
    {
        $config = $this->getConfiguration();
        $config_module     =   $this->scopeConfig
            ->getValue('disableEditProduct/general/enable', ScopeInterface::SCOPE_STORE);
        foreach ($this->getChildComponents() as $actionComponent) {
            $actionType = $actionComponent->getConfiguration()['type'];
            if ($this->isActionAllowed($actionType)) {
                // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                $config['actions'][] = array_merge($actionComponent->getConfiguration(), ['__disableTmpl' => true]);
            }
        }
        if ($config_module && $this->getContext()->getNamespace() === 'product_listing') {
            $config['disable_action'] = 'disabled';
        }
        $origConfig = $this->getConfiguration();
        if ($origConfig !== $config) {
            $config = array_replace_recursive($config, $origConfig);
        }

        $this->setData('config', $config);
        $this->components = [];

        parent::prepare();
    }
}
