<?php


namespace Mpx\Marketplace\Observer;

use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Webkul\Marketplace\Model\Product as ProductStatus;
use Webkul\Marketplace\Model\SaleperpartnerFactory as MpSalesPartner;
use Webkul\Marketplace\Model\SellerFactory as MpSeller;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Webkul\Marketplace\Helper\Email as MpEmailHelper;
use Magento\Catalog\Model\ProductFactory as ProductModel;
use Magento\Framework\Filesystem\Io\File as FilesystemIo;

/**
 * Mpx Marketplace AdminhtmlCustomerSaveAfterObserver Observer.
 */
class AdminhtmlCustomerSaveAfterObserver extends \Webkul\Marketplace\Observer\AdminhtmlCustomerSaveAfterObserver
{
    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $_messageManager;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $_mediaDirectory;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Product\Collection
     */
    protected $_sellerProduct;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $_jsonDecoder;

    /**
     * @var MpSalesPartner
     */
    protected $mpSalesPartner;

    /**
     * @var MpSeller
     */
    protected $mpSeller;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var MpHelper
     */
    protected $mpHelper;

    /**
     * @var MpEmailHelper
     */
    protected $mpEmailHelper;

    /**
     * @var \Webkul\Marketplace\Model\ProductFactory
     */
    protected $mpProductFactory;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $reader;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var ProductModel
     */
    protected $productModel;

    /**
     * @var FilesystemIo
     */
    protected $_filesystemFile;

    /**
     * @param Filesystem $filesystem
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param CollectionFactory $collectionFactory
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param ProductCollection $sellerProduct
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param MpSalesPartner $mpSalesPartner
     * @param MpSeller $mpSeller
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param MpHelper $mpHelper
     * @param MpEmailHelper $mpEmailHelper
     * @param \Webkul\Marketplace\Model\ProductFactory $mpProductFactory
     * @param \Magento\Framework\Module\Dir\Reader $reader
     * @param ProductModel $productModel
     * @param FilesystemIo|null $filesystemFile
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        Filesystem $filesystem,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        CollectionFactory $collectionFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        ProductCollection $sellerProduct,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        MpSalesPartner $mpSalesPartner,
        MpSeller $mpSeller,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        MpHelper $mpHelper,
        MpEmailHelper $mpEmailHelper,
        \Webkul\Marketplace\Model\ProductFactory $mpProductFactory,
        \Magento\Framework\Module\Dir\Reader $reader,
        ProductModel $productModel,
        FilesystemIo $filesystemFile = null
    ) {
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_productRepository = $productRepository;
        $this->_messageManager = $messageManager;
        $this->_collectionFactory = $collectionFactory;
        $this->_storeManager = $storeManager;
        $this->_date = $date;
        $this->_sellerProduct = $sellerProduct;
        $this->_jsonDecoder = $jsonDecoder;
        $this->mpSalesPartner = $mpSalesPartner;
        $this->mpSeller = $mpSeller;
        $this->customerFactory = $customerFactory;
        $this->mpHelper = $mpHelper;
        $this->mpEmailHelper = $mpEmailHelper;
        $this->mpProductFactory = $mpProductFactory;
        $this->reader = $reader;
        $this->filesystem = $filesystem;
        $this->productModel = $productModel;
        $this->_filesystemFile = $filesystemFile ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->create(FilesystemIo::class);

        parent::__construct(
            $filesystem,
            $date,
            $messageManager,
            $storeManager,
            $productRepository,
            $collectionFactory,
            $fileUploaderFactory,
            $sellerProduct,
            $jsonDecoder,
            $mpSalesPartner,
            $mpSeller,
            $customerFactory,
            $mpHelper,
            $mpEmailHelper,
            $mpProductFactory,
            $reader,
            $productModel,
            $filesystemFile
        );
    }

    /**
     * Admin customer save after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|AdminhtmlCustomerSaveAfterObserver|void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->moveDirToMediaDir();
        $customer = $observer->getCustomer();
        $customerid = $customer->getId();
        $postData = $observer->getRequest()->getPostValue();
        if ($this->isSeller($customerid)) {
            list($data, $errors) = $this->validateprofiledata($observer);
            $productIds = isset($postData['sellerassignproid']) ?
                $postData['sellerassignproid'] : '';
            $sellerId = $customerid;
            if (isset($postData['is_seller_remove'])
                && $postData['is_seller_remove'] == true) {
                $this->removePartner($sellerId);
                $this->_messageManager->addSuccess(
                    __('You removed the customer from seller.')
                );

                return $this;
            }
            if ($productIds != '' || $productIds != 0) {
                $this->assignProduct($sellerId, $productIds);
            }
            if (!empty($postData['commission_enable'])) {
                $collectionselect = $this->mpSalesPartner->create()
                    ->getCollection()
                    ->addFieldToFilter(
                        'seller_id',
                        $sellerId
                    );
                if ($collectionselect->getSize() == 1) {
                    foreach ($collectionselect as $verifyrow) {
                        $autoid = $verifyrow->getEntityId();
                    }

                    $collectionupdate = $this->mpSalesPartner->create()->load($autoid);
                    if (!isset($postData['commission'])) {
                        $postData['commission'] = $collectionupdate->getCommissionRate();
                    }
                    $collectionupdate->setCommissionRate($postData['commission']);
                    $collectionupdate->save();
                } else {
                    if (!isset($postData['commission'])) {
                        $postData['commission'] = 0;
                    }
                    $collectioninsert = $this->mpSalesPartner->create();
                    $collectioninsert->setSellerId($sellerId);
                    $collectioninsert->setCommissionRate($postData['commission']);
                    $collectioninsert->save();
                }
            }
            if (empty($errors)) {
                $target = $this->_mediaDirectory->getAbsolutePath('avatar/');
                // upload logo file
                $postData['banner_pic'] = $this->uploadSellerProfileImage(
                    $target,
                    'banner_pic'
                );

                // upload logo file
                $postData['logo_pic'] = $this->uploadSellerProfileImage(
                    $target,
                    'logo_pic'
                );

                $autoId = 0;
                $storeId = 0;
                if (!empty($postData['store_id'])) {
                    $storeId = $postData['store_id'];
                }
                $collection = $this->_collectionFactory->create()
                    ->addFieldToFilter('seller_id', $sellerId)
                    ->addFieldToFilter('store_id', $storeId);
                if (!count($collection)) {
                    $collection = $this->_collectionFactory->create()
                        ->addFieldToFilter('seller_id', $sellerId)
                        ->addFieldToFilter('store_id', 0);
                    foreach ($collection as $value) {
                        $postData['banner_pic'] = $postData['banner_pic'] ?
                            $postData['banner_pic'] : $value->getBannerPic();
                        $postData['logo_pic'] = $postData['logo_pic'] ?
                            $postData['logo_pic'] : $value->getLogoPic();
                    }
                    foreach ($collection as $value) {
                        $sellerDefaultData = $value->getData();
                        $postData['banner_pic'] = $postData['banner_pic'] ?
                            $postData['banner_pic'] : $value->getBannerPic();
                        $postData['logo_pic'] = $postData['logo_pic'] ?
                            $postData['logo_pic'] : $value->getLogoPic();
                    }
                    foreach ($sellerDefaultData as $key => $value) {
                        if (empty($postData[$key]) && $key != 'entity_id') {
                            $postData[$key] = $value;
                        }
                    }
                } else {
                    foreach ($collection as $value) {
                        $autoId = $value->getId();
                        $postData['banner_pic'] = $postData['banner_pic'] ?
                            $postData['banner_pic'] : $value->getBannerPic();
                        $postData['logo_pic'] = $postData['logo_pic'] ?
                            $postData['logo_pic'] : $value->getLogoPic();
                    }
                }
                $value = $this->mpSeller->create()->load($autoId);
                $value->addData($postData);
                $value->setIsSeller(1);
                $value->setUpdatedAt($this->_date->gmtDate());
                $value->save();
                if (isset($postData['seller_category_ids'])) {
                    $catIds = '';
                    foreach ($postData['seller_category_ids'] as $categoryId => $selected) {
                        if ($selected) {
                            $catIds = $catIds.$categoryId.',';
                        }
                    }
                    $catIds = rtrim($catIds, ',');
                    $value->setAllowedCategories($catIds);
                }
                if (isset($postData['company_description'])) {
                    $postData['company_description'] = preg_replace(
                        '#<script(.*?)>(.*?)</script>#is',
                        '',
                        $postData['company_description']
                    );
                    $value->setCompanyDescription(
                        $postData['company_description']
                    );
                }

                if (isset($postData['return_policy'])) {
                    $postData['return_policy'] = preg_replace(
                        '#<script(.*?)>(.*?)</script>#is',
                        '',
                        $postData['return_policy']
                    );
                    $value->setReturnPolicy($postData['return_policy']);
                }

                if (isset($postData['shipping_policy'])) {
                    $postData['shipping_policy'] = preg_replace(
                        '#<script(.*?)>(.*?)</script>#is',
                        '',
                        $postData['shipping_policy']
                    );
                    $value->setShippingPolicy($postData['shipping_policy']);
                }

                if (isset($postData['privacy_policy'])) {
                    $postData['privacy_policy'] = preg_replace(
                        '#<script(.*?)>(.*?)</script>#is',
                        '',
                        $postData['privacy_policy']
                    );
                    $value->setPrivacyPolicy($postData['privacy_policy']);
                }

                if (isset($postData['meta_description'])) {
                    $value->setMetaDescription($postData['meta_description']);
                }

                /**
                 * set taxvat number for seller
                 */
                if (isset($postData['taxvat'])) {
                    $customer = $this->customerFactory->create()->load($sellerId);
                    $customer->setTaxvat($postData['taxvat']);
                    $customer->setId($sellerId)->save();
                }

                if (array_key_exists('country_pic', $postData)) {
                    $value->setCountryPic($postData['country_pic']);
                }
                $value->save();
            }
        } else {
            $profileurl = '';
            $profiletitle = '';
            if (isset($postData['is_seller_add'])) {
                $isSellerAdd = $postData['is_seller_add'];
                $profileurl = $postData['profileurl'];
                $profiletitle = $postData['profiletitle'];
            } else {
                $isSellerAdd = false;
            }
            if ($profileurl != '' && $profiletitle != '') {
                $profileurlcount = $this->_collectionFactory->create();
                $profileurlcount->addFieldToFilter('shop_url', $profileurl);
                $profileurlcount->addFieldToFilter('shop_title', $profiletitle);
                $sellerProfileIds = [];
                $sellerProfileUrl = '';
                $collectionselect = $this->_collectionFactory->create();
                $collectionselect->addFieldToFilter('seller_id', $customerid);
                foreach ($collectionselect as $coll) {
                    array_push($sellerProfileIds, $coll->getEntityId());
                    $sellerProfileUrl = $coll->getShopUrl();
                    $sellerProfileTitle = $coll->getShopTitle();
                }
                if ($profileurlcount->getSize() && ($profileurl != $sellerProfileUrl) && ($profiletitle != $sellerProfileTitle)) {
                    $this->_messageManager->addError(
                        __('This Shop URL already Exists.')
                    );
                } else {
                    $sellerStatus = $isSellerAdd ? 1 : 0;
                    if (!empty($sellerProfileIds)) {
                        foreach ($sellerProfileIds as $sellerProfileId) {
                            $collection = $this->mpSeller->create()->load($sellerProfileId);
                            $collection->setIsSeller($sellerStatus);
                            $collection->setShopUrl($profileurl);
                            $collection->setShopTitle($profiletitle);
                            $collection->setSellerId($customerid);
                            $collection->setCreatedAt($this->_date->gmtDate());
                            $collection->setUpdatedAt($this->_date->gmtDate());
                            $collection->save();
                        }
                    } else {
                        $sellerProfileId = 0;
                        $collection = $this->mpSeller->create()->load($sellerProfileId);
                        $collection->setIsSeller($sellerStatus);
                        $collection->setShopUrl($profileurl);
                        $collection->setShopTitle($profiletitle);
                        $collection->setStoreId(0);
                        $collection->setSellerId($customerid);
                        $collection->setCreatedAt($this->_date->gmtDate());
                        $collection->setUpdatedAt($this->_date->gmtDate());
                        $collection->save();
                    }
                    if ($sellerStatus) {
                        $helper = $this->mpHelper;
                        $adminStoreEmail = $helper->getAdminEmailId();
                        $adminEmail = $adminStoreEmail ? $adminStoreEmail :
                            $helper->getDefaultTransEmailId();
                        $adminUsername = $helper->getAdminName();

                        $seller = $this->customerFactory->create()->load($customerid);
                        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
                        $emailTempVariables['myvar1'] = $seller->getName();
                        $emailTempVariables['myvar2'] = $baseUrl.'customer/account/login';
                        $senderInfo = [
                            'name' => $adminUsername,
                            'email' => $adminEmail,
                        ];
                        $receiverInfo = [
                            'name' => $seller->getName(),
                            'email' => $seller->getEmail(),
                        ];
                        $this->mpEmailHelper->sendSellerApproveMail(
                            $emailTempVariables,
                            $senderInfo,
                            $receiverInfo
                        );
                    }
                    $this->_messageManager->addSuccess(
                        __('You created the customer as seller.')
                    );
                }
            }
        }

        return $this;
    }

    /**
     * @param $observer
     * @return array
     */
    private function validateprofiledata($observer)
    {
        $errors = [];
        $data = [];
        $paramData = $observer->getRequest()->getParams();
        foreach ($paramData as $code => $value) {
            switch ($code):
                case 'twitter_id':
                    if (trim($value) != '' && preg_match('/[\'^£$%&*()}{@#~?><>, |=_+¬-]/', $value)) {
                        $errors[] = __(
                            'Twitter Id cannot contain space and special characters'
                        );
                    } else {
                        $data[$code] = $value;
                    }
                    break;
                case 'facebook_id':
                    if (trim($value) != '' && preg_match('/[\'^£$%&*()}{@#~?><>, |=_+¬-]/', $value)) {
                        $errors[] = __(
                            'Facebook Id cannot contain space and special characters'
                        );
                    } else {
                        $data[$code] = $value;
                    }
            endswitch;
        }

        return [$data, $errors];
    }

    /**
     * Remove Partner
     *
     * @param $sellerId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function removePartner($sellerId)
    {
        $collectionselectdelete = $this->mpSeller->create()->getCollection();
        $collectionselectdelete->addFieldToFilter(
            'seller_id',
            $sellerId
        );
        foreach ($collectionselectdelete as $sellerColl) {
            $sellerColl->delete();
        }
        //Set Produt status disabled
        $sellerProduct = $this->_sellerProduct->create()
            ->addFieldToFilter(
                'seller_id',
                $sellerId
            );

        foreach ($sellerProduct as $productInfo) {
            $allStores = $this->_storeManager->getStores();
            foreach ($allStores as $_eachStoreId => $val) {
                try {
                    $product = $this->_productRepository->getById(
                        $productInfo->getMageproductId()
                    );
                    $product->setStatus(
                        ProductStatus::STATUS_DISABLED
                    );
                    $this->_productRepository->save($product);
                } catch (\Exception $e) {
                    $this->mpHelper->logDataInLogger(
                        "Observer_AdminhtmlCustomerSaveAfterObserver removePartner : ".$e->getMessage()
                    );
                }
            }

            $productInfo->delete();
        }

        $helper = $this->mpHelper;
        $adminStoreEmail = $helper->getAdminEmailId();
        $adminEmail = $adminStoreEmail ?
            $adminStoreEmail : $helper->getDefaultTransEmailId();
        $adminUsername = $helper->getAdminName();

        $seller = $this->customerFactory->create()->load($sellerId);

        $emailTempVariables['myvar1'] = $seller->getName();
        $emailTempVariables['myvar2'] = $this->_storeManager->getStore()->getBaseUrl().'marketplace/account/login';
        $senderInfo = [
            'name' => $adminUsername,
            'email' => $adminEmail,
        ];
        $receiverInfo = [
            'name' => $seller->getName(),
            'email' => $seller->getEmail(),
        ];
        $this->mpEmailHelper->sendSellerDisapproveMail(
            $emailTempVariables,
            $senderInfo,
            $receiverInfo
        );
    }

    /**
     * Move DirToMediaDir
     *
     * @param $value
     * @return void
     */
    private function moveDirToMediaDir($value = '')
    {
        try {
            $reader = $this->reader;

            /** @var \Magento\Framework\Filesystem $filesystem */
            $filesystem = $this->filesystem;

            $mediaAvatarFullPath = $filesystem->getDirectoryRead(
                \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
            )->getAbsolutePath('avatar');
            if (!file_exists($mediaAvatarFullPath)) {
                mkdir($mediaAvatarFullPath, 0777, true);
                $avatarBannerImage = $reader->getModuleDir(
                        '',
                        'Webkul_Marketplace'
                    ).'/view/base/web/images/avatar/banner-image.png';
                copy($avatarBannerImage, $mediaAvatarFullPath.'/banner-image.png');
                $avatarNoImage = $reader->getModuleDir(
                        '',
                        'Webkul_Marketplace'
                    ).'/view/base/web/images/avatar/noimage.png';
                copy($avatarNoImage, $mediaAvatarFullPath.'/noimage.png');
            }

            $mediaMarketplaceFullPath = $filesystem->getDirectoryRead(
                \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
            )->getAbsolutePath('marketplace');
            if (!file_exists($mediaMarketplaceFullPath)) {
                mkdir($mediaMarketplaceFullPath, 0777, true);
            }

            $mediaMarketplaceBannerFullPath = $filesystem->getDirectoryRead(
                \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
            )->getAbsolutePath('marketplace/banner');
            if (!file_exists($mediaMarketplaceBannerFullPath)) {
                mkdir($mediaMarketplaceBannerFullPath, 0777, true);
                $marketplaceBannerImage = $reader->getModuleDir(
                        '',
                        'Webkul_Marketplace'
                    ).'/view/base/web/images/marketplace/banner/sell-page-banner.png';
                copy(
                    $marketplaceBannerImage,
                    $mediaMarketplaceBannerFullPath.'/sell-page-banner.png'
                );
                // for landing page layout 2
                $marketplaceBannerImage = $reader->getModuleDir(
                        '',
                        'Webkul_Marketplace'
                    ).'/view/base/web/images/landingpage1/banner/sell-page-1-hero-banner.jpg';
                $this->_filesystemFile->cp(
                    $marketplaceBannerImage,
                    $mediaMarketplaceBannerFullPath.'/sell-page-1-hero-banner.jpg'
                );
                // for landing page layout 3
                $marketplaceBannerImage = $reader->getModuleDir(
                        '',
                        'Webkul_Marketplace'
                    ).'/view/base/web/images/landingpage2/banner/sell-page-2-hero-banner.jpg';
                $this->_filesystemFile->cp(
                    $marketplaceBannerImage,
                    $mediaMarketplaceBannerFullPath.'/sell-page-2-hero-banner.jpg'
                );
            }

            $mediaMarketplaceIconFullPath = $filesystem->getDirectoryRead(
                \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
            )->getAbsolutePath('marketplace/icon');
            if (!file_exists($mediaMarketplaceIconFullPath)) {
                mkdir($mediaMarketplaceIconFullPath, 0777, true);
                $icon1BannerImage = $reader->getModuleDir(
                        '',
                        'Webkul_Marketplace'
                    ).'/view/base/web/images/marketplace/icon/icon-add-products.png';
                copy(
                    $icon1BannerImage,
                    $mediaMarketplaceIconFullPath.'/icon-add-products.png'
                );

                $icon2BannerImage = $reader->getModuleDir(
                        '',
                        'Webkul_Marketplace'
                    ).'/view/base/web/images/marketplace/icon/icon-collect-revenues.png';
                copy(
                    $icon2BannerImage,
                    $mediaMarketplaceIconFullPath.'/icon-collect-revenues.png'
                );

                $icon3BannerImage = $reader->getModuleDir(
                        '',
                        'Webkul_Marketplace'
                    ).'/view/base/web/images/marketplace/icon/icon-register-yourself.png';
                copy(
                    $icon3BannerImage,
                    $mediaMarketplaceIconFullPath.'/icon-register-yourself.png'
                );

                $icon4BannerImage = $reader->getModuleDir(
                        '',
                        'Webkul_Marketplace'
                    ).'/view/base/web/images/marketplace/icon/icon-start-selling.png';
                copy(
                    $icon4BannerImage,
                    $mediaMarketplaceIconFullPath.'/icon-start-selling.png'
                );

                // for landing page layout 3
                $iconBannerImage = $reader->getModuleDir(
                        '',
                        'Webkul_Marketplace'
                    ).'/view/base/web/images/landingpage2/icon/sell-page-2-setup-1.png';
                $this->_filesystemFile->cp(
                    $iconBannerImage,
                    $mediaMarketplaceIconFullPath.'/sell-page-2-setup-1.png'
                );
                $iconBannerImage = $reader->getModuleDir(
                        '',
                        'Webkul_Marketplace'
                    ).'/view/base/web/images/landingpage2/icon/sell-page-2-setup-2.png';
                $this->_filesystemFile->cp(
                    $iconBannerImage,
                    $mediaMarketplaceIconFullPath.'/sell-page-2-setup-2.png'
                );
                $iconBannerImage = $reader->getModuleDir(
                        '',
                        'Webkul_Marketplace'
                    ).'/view/base/web/images/landingpage2/icon/sell-page-2-setup-3.png';
                $this->_filesystemFile->cp(
                    $iconBannerImage,
                    $mediaMarketplaceIconFullPath.'/sell-page-2-setup-3.png'
                );
                $iconBannerImage = $reader->getModuleDir(
                        '',
                        'Webkul_Marketplace'
                    ).'/view/base/web/images/landingpage2/icon/sell-page-2-setup-4.png';
                $this->_filesystemFile->cp(
                    $iconBannerImage,
                    $mediaMarketplaceIconFullPath.'/sell-page-2-setup-4.png'
                );
                $iconBannerImage = $reader->getModuleDir(
                        '',
                        'Webkul_Marketplace'
                    ).'/view/base/web/images/landingpage2/icon/sell-page-2-setup-5.png';
                $this->_filesystemFile->cp(
                    $iconBannerImage,
                    $mediaMarketplaceIconFullPath.'/sell-page-2-setup-5.png'
                );
            }

            $mediaPlaceholderFullPath = $filesystem->getDirectoryRead(
                \Magento\Framework\App\Filesystem\DirectoryList::MEDIA
            )->getAbsolutePath('placeholder');
            if (!file_exists($mediaPlaceholderFullPath)) {
                mkdir($mediaPlaceholderFullPath, 0777, true);
                $placeholderImage = $reader->getModuleDir(
                        '',
                        'Webkul_Marketplace'
                    ).'/view/base/web/images/placeholder/image.jpg';
                copy(
                    $placeholderImage,
                    $mediaPlaceholderFullPath.'/image.jpg'
                );
            }
        } catch (\Exception $e) {
            $this->mpHelper->logDataInLogger(
                "Observer_AdminhtmlCustomerSaveAfterObserver moveDirToMediaDir : ".$e->getMessage()
            );
            $this->_messageManager->addError($e->getMessage());
        }
    }
}
