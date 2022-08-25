<?php

/**
 * Webkul Mpshipping Shipping Edit Tab Shipping Admin Block
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpshipping\Block\Adminhtml\Shipping\Edit\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Webkul\Mpshipping\Model\ResourceModel\Mpshipping\CollectionFactory;

class Shipping extends \Magento\Backend\Block\Widget\Grid\Extended
{
    protected $_storeManager;
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    /**
     * @var \Webkul\Mpshipping\Model\MpshippingFactory
     */
    protected $_shippingFactory;
    /**
     * @var \Webkul\Mpshipping\Model\ResourceModel\Mpshipping\CollectionFactory
     */
    protected $_mpshippingFactoryCollection;
    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Webkul\Mpshipping\Model\MpshippingFactory $shippingFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Webkul\Mpshipping\Model\MpshippingFactory $shippingFactory,
        \Magento\Framework\Registry $coreRegistry,
        CollectionFactory $mpshippingCollection,
        array $data = []
    ) {
        $this->_shippingFactory = $shippingFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_storeManager = $context->getStoreManager();
        $this->_mpshippingFactoryCollection = $mpshippingCollection;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('mpshipping_grid');
        $this->setDefaultSort('mpshipping_id');
        $this->setUseAjax(true);
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $customerEntityTable = $this->_mpshippingFactoryCollection
            ->create()
            ->getTable('customer_grid_flat');
        $mpshippingMethodTable = $this->_mpshippingFactoryCollection
        ->create()
        ->getTable('marketplace_shippinglist_method');
        $collection = $this->_shippingFactory->create()->getCollection();
        $collection->getSelect()
            ->join(
                $mpshippingMethodTable.' as method',
                'main_table.shipping_method_id = method.entity_id',
                [
                    'method_name'=>'method_name'
                ]
            );
        $collection->getSelect()
            ->joinLeft(
                $customerEntityTable.' as customer',
                'main_table.partner_id = customer.entity_id',
                [
                    'customer_name'=>'name'
                ]
            );
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'mpshipping_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'mpshipping_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
                'type'=>'range'
            ]
        );
        $this->addColumn(
            'customer_name',
            [
                'header' => __('Name'),
                'sortable' => true,
                'filter'    =>  false,
                'index' => 'customer_name',
                'renderer'  => \Webkul\Mpshipping\Block\Adminhtml\Shipping\Grid\Rendername::class,
            ]
        );
        $this->addColumn(
            'dest_country_id',
            [
                'header' => __('Destination Country'),
                'sortable' => true,
                'index' => 'dest_country_id'
            ]
        );
        $this->addColumn(
            'dest_region_id',
            [
                'header' => __('Destination Region'),
                'sortable' => true,
                'index' => 'dest_region_id'
            ]
        );
        $this->addColumn(
            'dest_zip',
            [
                'header' => __('Zip Code From'),
                'sortable' => true,
                'index' => 'dest_zip',
                'type'=>'range'

            ]
        );
        $this->addColumn(
            'dest_zip_to',
            [
                'header' => __('Zip Code To'),
                'sortable' => true,
                'index' => 'dest_zip_to',
                'type'=>'range'

            ]
        );
        $this->addColumn(
            'price',
            [
                'type'  =>'range',
                'header' => __('Price'),
                'sortable' => true,
                'index' => 'price'
            ]
        );
        $this->addColumn(
            'weight_from',
            [
                'header' => __('Weight From'),
                'sortable' => true,
                'index' => 'weight_from',
                'type'=>'range'

            ]
        );
        $this->addColumn(
            'weight_to',
            [
                'header' => __('Weight To'),
                'sortable' => true,
                'index' => 'weight_to',
                'type'=>'range'

            ]
        );
        $this->addColumn(
            'is_range',
            [
                'header' => __('Numeric Zipcode'),
                'sortable' => true,
                'index' => 'is_range'
            ]
        );
        $this->addColumn(
            'zipcode',
            [
                'header' => __('Alphanumeric Zipcode'),
                'sortable' => true,
                'index' => 'zipcode',
                'type'=>'range'
            ]
        );
        $this->addColumn(
            'method_name',
            [
                'header' => __('Sub Method Name'),
                'sortable' => true,
                'index' => 'method_name'
            ]
        );
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->getMassactionBlock()->setTemplate('Webkul_Mpshipping::widget/grid/massaction_extended.phtml');
        $this->setMassactionIdField('mpshipping_id');
        $this->getMassactionBlock()->setFormFieldName('mpshipping');
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label'    => __('Delete'),
                'url'      => $this->getUrl('*/*/massDelete', ['_current' => true]),
                'confirm'  => __('Are you sure?')
            ]
        );
        return $this;
    }
    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('mpshipping/*/gridshipping', ['_current' => true]);
    }
    /**
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('mpshipping/*/edit', ['_current' => true, 'id'=> $row->getMpshippingId()]);
    }
    public function getcurrency()
    {
        return $currencyCode = $this->_storeManager->getStore()->getBaseCurrencyCode();
    }
}
