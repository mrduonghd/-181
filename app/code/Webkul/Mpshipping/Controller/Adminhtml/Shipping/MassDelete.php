<?php
/**
 * Mpshipping Admin Shipping massDelete Controller
 *
 * @category  Webkul
 * @package   Webkul_Mpshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpshipping\Controller\Adminhtml\Shipping;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Webkul\Mpshipping\Model\ResourceModel\Mpshipping\CollectionFactory;
use Webkul\Mpshipping\Model\Mpshipping;

/**
 * Class MassDelete is used for shipping rule mass delete
 */
class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * Field id
     */
    const ID_FIELD = 'mpshipping_id';

    /**
     * Resource collectionFactory
     *
     * @var string
     */
    protected $collectionFactory;

    /**
     * Resource mpshipping
     *
     * @var string
     */
    protected $mpshipping;

    /**
     * @param \Magento\Backend\App\Action\Context                      $context
     * @param CollectionFactory                                        $collectionFactory
     * @param Mpshipping                                               $mpshipping
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        Mpshipping $mpshipping
    ) {
      
        $this->collectionFactory = $collectionFactory;
        $this->mpshipping = $mpshipping;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!empty($params['mpshipping'])) {
            try {
                $selected = $params['mpshipping'];
                $this->selectedDelete($selected, $params);
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            $this->messageManager->addError(__('Please select item(s).'));
        }
        return $resultRedirect->setPath('mpshipping/*/index');
    }

    /**
     * Delete selected items
     *
     * @param array $selected
     * @return void
     * @throws \Exception
     */
    protected function selectedDelete(array $selected, $params)
    {
        /** @var AbstractCollection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addFieldToFilter(static::ID_FIELD, ['in' => $selected]);
        $this->setSuccessMessage($this->delete($collection, $params));
    }

    /**
     * Delete collection items
     *
     * @param AbstractCollection $collection
     * @return int
     */
    protected function delete(AbstractCollection $collection, $params)
    {
        $count = 0;
        foreach ($collection->getAllIds() as $id) {
            $model = $this->mpshipping->load($id);
            $model->delete();
            ++$count;
        }
        return $count;
    }
    
    /**
     * Set error messages
     *
     * @param int $count
     * @return void
     */
    protected function setSuccessMessage($count)
    {
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $count));
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Mpshipping::mpshipping');
    }
}
