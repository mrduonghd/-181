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
namespace Webkul\MpTimeDelivery\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\SchemaPatchInterface;
use Magento\Framework\Setup\Patch\PatchRevertableInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Webkul\Marketplace\Model\ControllersRepository;
use Magento\Customer\Model\Customer;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class MptimedeliveryControllers implements
    DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * @var \Magento\Customer\Setup\CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    private $attributeSetFactory;

    /**
     * @var Webkul\Marketplace\Model\ControllersRepository
     */
    private $controllersRepository;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     * @param ControllersRepository $controllersRepository
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        AttributeSetFactory $attributeSetFactory,
        ControllersRepository $controllersRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
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
         * @var CustomerSetup $customerSetup
         */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /**
         * @var $attributeSet AttributeSet
         */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $frontendClass = '';
        $customerSetup->addAttribute(
            Customer::ENTITY,
            'minimum_time_required',
            [
                'type' => 'varchar',
                'label' => 'Minimum time in order process',
                'input' => 'text',
                'frontend_class' => $frontendClass,
                'required' => false,
                'visible' => false,
                'user_defined' => true,
                'sort_order' => 1000,
                'position' => 1000,
                'system' => 0,
            ]
        );

        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, 'minimum_time_required')
            ->addData(
                [
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => [],
                ]
            );

        $attribute->save();

        $setup=$this->moduleDataSetup;
        $setup->startSetup();

        /**
         * insert sellerstorepickup controller's data
         */
        $data = [];

        if (!($this->controllersRepository->getByPath('timedelivery/account/index')->getSize())) {
            $data[] = [
                'module_name' => 'Webkul_MpTimeDelivery',
                'controller_path' => 'timedelivery/account/index',
                'label' => 'Time Delivery Configuration',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        if (!($this->controllersRepository->getByPath('timedelivery/seller/orders')->getSize())) {
            $data[] = [
                'module_name' => 'Webkul_MpTimeDelivery',
                'controller_path' => 'timedelivery/seller/orders',
                'label' => 'Delivery Order History',
                'is_child' => '0',
                'parent_id' => '0',
            ];
        }

        $setup->getConnection()
            ->insertMultiple($setup->getTable('marketplace_controller_list'), $data);

        $setup->endSetup();
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
