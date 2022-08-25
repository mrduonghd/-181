<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpshipping\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Webkul\Marketplace\Model\ControllersRepository;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class ControllersDataUpdate implements
    DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;

    /**
     * @var ControllersRepository
     */
    private $controllersRepository;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param ControllersRepository $controllersRepository
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        ControllersRepository $controllersRepository
    ) {
    
        $this->moduleDataSetup = $moduleDataSetup;
        $this->controllersRepository = $controllersRepository;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        /**
         * insert Table Rate Shipping controller's data
         */
        $data = [];
        // setup default
        $this->moduleDataSetup->getConnection()->startSetup();
        $connection = $this->moduleDataSetup->getConnection();
        if (!count($this->controllersRepository->getByPath('mpshipping/distanceset/view'))) {
            $data[] = [
               'module_name' => 'Webkul_Mpshipping',
               'controller_path' => 'mpshipping/distanceset/view',
               'label' => 'Shipping By Distnace',
               'is_child' => '0',
               'parent_id' => '0',
            ];
        }
        if (!count($this->controllersRepository->getByPath('mpshipping/seller/location'))) {
            $data[] = [
               'module_name' => 'Webkul_Mpshipping',
               'controller_path' => 'mpshipping/seller/location',
               'label' => 'Set Location',
               'is_child' => '0',
               'parent_id' => '0',
            ];
        }
        if (!empty($data)) {
            $connection->insertMultiple($this->moduleDataSetup->getTable('marketplace_controller_list'), $data);
        }

        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }
}
