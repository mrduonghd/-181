<?php
/**
 * Mpshipping Admin Shippingset massDelete Controller
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpshipping\Controller\Adminhtml\Shippingset;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\Mpshipping\Model\ResourceModel\Mpshippingset\CollectionFactory;

/**
 * Class MassDelete for shipping set's rule
 */
class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * @var Filter
     */
    protected $_filter;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param Context $context
     * @param Filter  $filter
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->_filter = $filter;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context);
    }
    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        //$collection = $this->_collectionFactory->create();
        $collection = $this->_filter->getCollection($this->_collectionFactory->create());
        $countRecord = $collection->getSize();
        foreach ($collection as $item) {
            $item->delete();
        }
        $this->messageManager->addSuccess(
            __(
                'A total of %1 record(s) have been deleted.',
                $countRecord
            )
        );

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('mpshipping/*/index');
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Mpshipping::mpshippingset');
    }
}
