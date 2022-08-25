<?php
namespace Mpx\Sales\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Webkul\Marketplace\Model\SellerFactory;
use Webkul\Marketplace\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;

class AddCustomVariableSeller implements ObserverInterface
{
    /**
     * @var SellerFactory
     */
    protected $seller;

    /**
     * @var Data
     */
    protected $data;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param SellerFactory $seller
     * @param Data $data
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        SellerFactory $seller,
        Data $data,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->seller = $seller;
        $this->data = $data;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Set data shop url in mail new order
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer) :void
    {
        $transportObject = $observer->getData('transportObject');
        $productId = $transportObject->getOrder()->getItems()[0]->getProductId();
        $sellerId = $this->data->getSellerIdByProductId($productId);
        $seller = $this->seller->create()->getCollection()
            ->addFieldToFilter('seller_id', $sellerId)
            ->addFieldToFilter('store_id', 1)
            ->getFirstItem();
        $shopTitle = $seller->getShopTitle();
        $shopUrl = $seller->getShopUrl();
        $transportObject['shop_title'] = $shopTitle ?? '';
        $transportObject['shop_page_url'] = $this->getUrl($shopUrl ?? '');
    }

    /**
     * Get Url
     *
     * @param string $shopPageUrl
     * @return string
     */
    public function getUrl(string $shopPageUrl): string
    {
        try {
            $store = $this->storeManager->getStore();
            if ($store) {
                $url =  $store->getBaseUrl();
                return $url."marketplace/seller/profile/shop/".$shopPageUrl;
            }
        } catch (\Exception $exception) {
            $this->logger->critical($exception);
            return "";
        }
        return "";
    }
}
