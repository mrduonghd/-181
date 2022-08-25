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
namespace Webkul\MpTimeDelivery\Helper;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use \Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\SessionFactory;
use Magento\Quote\Model\Quote\Item\OptionFactory;
use Webkul\Marketplace\Model\ProductFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\ScopeInterface;
use Webkul\MpAssignProduct\Model\Items;
use Magento\GiftMessage\Helper\Message;

/**
 * MpTimeDelivery data helper.
 */
class Data extends AbstractHelper implements ArgumentInterface
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSessionFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Item\OptionFactory
     */
    protected $itemOptionFactory;

    /**
     * @var \Webkul\Marketplace\Model\ProductFactory
     */
    protected $mpProductFactory;

    /**
     * @var Magento\Framework\Serialize\Serializer\Json
     */
    protected $json;

    /**
     * @var Message
     */
    protected $giftMessage;

    /**
     * @param Context $context
     * @param ObjectManagerInterface $objectManager
     * @param StoreManagerInterface $storeManager
     * @param SessionFactory $customerSessionFactory
     * @param OptionFactory $itemOptionFactory
     * @param ProductFactory $mpProductFactory
     * @param Json $json
     * @param Message $giftMessage
     */
    public function __construct(
        Context $context,
        ObjectManagerInterface $objectManager,
        StoreManagerInterface $storeManager,
        SessionFactory $customerSessionFactory,
        OptionFactory $itemOptionFactory,
        ProductFactory $mpProductFactory,
        Json $json,
        Message $giftMessage
    ) {
        parent::__construct($context);
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->customerSessionFactory = $customerSessionFactory;
        $this->itemOptionFactory = $itemOptionFactory;
        $this->mpProductFactory = $mpProductFactory;
        $this->scopeConfig = $context->getScopeConfig();
        $this->json = $json;
        $this->giftMessage = $giftMessage;
    }

    /**
     * Retrieve information from carrier configuration.
     *
     * @param string $field
     *
     * @return void|false|string
     */
    public function getConfigData($field)
    {
        $path = 'timeslot/configurations/'.$field;

        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()
        );
    }

    /**
     * return current customer session.
     *
     * @return \Magento\Customer\Model\Session
     */
    public function _getCustomerData()
    {
        return $this->customerSessionFactory->create()->getCustomer();
    }

    /**
     * get assign product id.
     *
     * @param object $item
     *
     * @return int
     */
    public function getAssignProduct($item)
    {
        $mpAssignProductId = 0;
        $itemOption = $this->itemOptionFactory->create()
                            ->getCollection()
                            ->addFieldToFilter('item_id', ['eq' => $item->getId()])
                            ->addFieldToFilter('code', ['eq' => 'info_buyRequest']);
        
        $optionValue = '';
        if ($itemOption->getSize()) {
            foreach ($itemOption as $value) {
                $optionValue = $value->getValue();
            }
        }
        if ($optionValue != '') {
            $mpAssignProduct = $this->json->unserialize($optionValue, true);
            if (isset($mpAssignProduct['mpassignproduct_id'])) {
                $mpAssignProductId = $mpAssignProduct['mpassignproduct_id'];
            }
        }

        return $mpAssignProductId;
    }

    /**
     * get seller id.
     *
     * @param int $mpAssignProductId
     * @param int $productId
     *
     * @return int
     */
    public function getSellerId($mpAssignProductId, $productId)
    {
        $sellerId = 0;
        if ($mpAssignProductId) {
            $mpAssignModel = $this->objectManager->create(
                Items::class
            )->load($mpAssignProductId);
            $sellerId = $mpAssignModel->getSellerId();
        } else {
            $collection = $this->mpProductFactory->create()
                ->getCollection()
                ->addFieldToFilter('mageproduct_id', ['eq' => $productId]);
            foreach ($collection as $mpProduct) {
                $sellerId = $mpProduct->getSellerId();
            }
        }

        return $sellerId;
    }

    /**
     * Save Object
     *
     * @param object $object
     * @return void
     */
    public function saveObject($object)
    {
        $object->save();
    }

    /**
     * Delete Object
     *
     * @param object $object
     * @return void
     */
    public function deleteObject($object)
    {
        $object->delete();
    }

    /**
     * Get Json Helper
     *
     * @return object
     */
    public function getJson()
    {
        return $this->json;
    }
    
    /**
     * Get giftMessage object
     *
     * @return object
     */
    public function getGiftMessageObject()
    {
        return $this->giftMessage;
    }
}
