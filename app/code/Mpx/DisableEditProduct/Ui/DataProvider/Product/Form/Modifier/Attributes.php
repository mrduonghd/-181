<?php

namespace Mpx\DisableEditProduct\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\Store\Model\ScopeInterface;

/**
 * Disable fide edit product equal modifyData
 */
class Attributes extends AbstractModifier
{
    /**
     * The field to disable
     *
     * @var array
     */
    protected $disabledField = [
        'sku', 'tax_class_id',
        'price', 'name',
        'visibility', 'status',
        'attribute_set','attribute_set_id',
        'quantity_and_stock_status_qty',
        'quantity_and_stock_status', 'product-details',
        'product_has_weight', 'category_ids',
        'create_category_button', 'news_from_date',
        'news_to_date','country_of_manufacture',
        'mp_product_cart_limit', 'assignseller_field',
        'create_configurable_products_button',
        'description', 'short_description',
        'button_related', 'button_upsell',
        'button_crosssell', 'button_import',
        'button_add', 'websites', 'url_key',
        'meta_title', 'meta_keyword',
        'meta_description','page_layout',
        'options_container', 'custom_layout_update_file',
        'custom_design_from', 'custom_design_to',
        'custom_design', 'custom_layout',
        'gift_message_available', 'information-block2',
        'import_options_modal'
    ];
    protected $request;

    /**
     * @var ArrayManager
     */
    protected $arrayManager;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param ArrayManager $arrayManager
     * @param Http $request
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ArrayManager $arrayManager,
        Http $request,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
        $this->arrayManager = $arrayManager;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data): array
    {
        return $data;
    }

    /**
     * Handle meta data and modify result
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta): array
    {
        $config =   $this->scopeConfig
            ->getValue('disableEditProduct/general/enable', ScopeInterface::SCOPE_STORE);
        $controller = $this->request->getControllerName();
        $action     = $this->request->getActionName();
        $route      = $this->request->getRouteName();
        if ($config) {
            if (!empty($this->disabledField) && $controller === 'product'
                && $action === 'edit' && $route === "catalog") {
                foreach ($this->disabledField as $fieldPath) {
                    $meta = $this->disableFieldByPath($fieldPath, $meta);
                }
            }
            return $meta;
        }
        return $meta;
    }

    /**
     * Disable field by path and return modified meta
     *
     * @param string $fieldPath
     * @param array $meta
     * @return array
     */
    public function disableFieldByPath(string $fieldPath, array $meta): array
    {
        $targetField = $this->arrayManager->findPath($fieldPath, $meta, null, 'children');
        $configPath = $this->arrayManager->findPath('config', $meta, $targetField);
        return $this->arrayManager->set($configPath . '/disabled', $meta, 'true');
    }
}
