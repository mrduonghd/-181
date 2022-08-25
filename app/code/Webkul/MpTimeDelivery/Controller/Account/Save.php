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
namespace Webkul\MpTimeDelivery\Controller\Account;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterfaceFactory;
use Webkul\MpTimeDelivery\Api\TimeslotConfigRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Webkul\MpTimeDelivery\Model\ResourceModel\TimeSlotConfig\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\RequestInterface;
use Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface;

class Save extends Action
{
    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $_customerSessionFactory;
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $_timeSlotRepository;

    /**
     * @var TimeslotConfigInterface
     */
    protected $_timeSlotDataFactory;

     /**
      * @var CollectionFactory
      */
    protected $_timeSlotCollection;

    /**
     * @var DataObjectHelper
     */
    protected $_dataObjectHelper;

    /**
     * @var \Magento\Customer\Model\UrlFactory
     */
    protected $_urlFactory;
    
    /**
     * @param Context                                       $context
     * @param PageFactory                                   $resultPageFactory
     * @param TimeslotConfigRepositoryInterface             $timeSlotRepository
     * @param CollectionFactory                             $timeSlotCollection
     * @param TimeslotConfigInterfaceFactory                $timeSlotDataFactory
     * @param DataObjectHelper                              $dataObjectHelper
     * @param \Magento\Customer\Model\SessionFactory        $customerSessionFactory
     * @param CustomerRepositoryInterface                   $customerRepository
     * @param \Magento\Customer\Model\Customer\Mapper       $customerMapper
     * @param \Magento\Customer\Model\UrlFactory            $urlFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime   $dateTime,
     * @param CustomerInterfaceFactory                      $customerDataFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        TimeslotConfigRepositoryInterface $timeSlotRepository,
        CollectionFactory $timeSlotCollection,
        TimeslotConfigInterfaceFactory $timeSlotDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Magento\Customer\Model\SessionFactory $customerSessionFactory,
        CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Customer\Mapper $customerMapper,
        \Magento\Customer\Model\UrlFactory $urlFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        CustomerInterfaceFactory $customerDataFactory
    ) {
        $this->_customerSessionFactory = $customerSessionFactory;
        $this->_timeSlotRepository = $timeSlotRepository;
        $this->_timeSlotDataFactory = $timeSlotDataFactory;
        $this->_timeSlotCollection = $timeSlotCollection;
        $this->_customerRepository = $customerRepository;
        $this->_customerDataFactory = $customerDataFactory;
        $this->_customerMapper = $customerMapper;
        $this->_dataObjectHelper = $dataObjectHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->_urlFactory = $urlFactory;
        $this->_dateTime = $dateTime;
        parent::__construct($context);
    }

    /**
     * Check customer authentication
     *
     * @param  RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_urlFactory->create()->getLoginUrl();

        if (!$this->_customerSessionFactory->create()->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Default seller slot config Page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            $timeSlotData = $this->getRequest()->getParam('timedelivery');
            $validate = $this->validateData();
            $this->saveMinimumOrderTime();
            $count = 0;
            if (!$validate['error'] && isset($timeSlotData['slot'])) {
                $customerId = $this->_customerSessionFactory->create()->getCustomerId();
                foreach ($timeSlotData['slot'] as $data) {
                    $data['start_time'] =  $this->_dateTime->gmtDate('H:i', $data['start_time']);
                    $data['end_time'] =  $this->_dateTime->gmtDate('H:i', $data['end_time']);

                    if (isset($data['is_delete']) && $data['is_delete']) {
                        $this->deleteDataById($data['entity_id']);
                        continue;
                    }
                    $completeDataObject = $this->_timeSlotDataFactory->create();

                    $this->_dataObjectHelper->populateWithArray(
                        $completeDataObject,
                        $data,
                        TimeslotConfigInterface::class
                    );

                    $this->saveData($completeDataObject);
                    $count++;
                }

                $this->messageManager->addSuccess(__('Total %1 time slot(s) saved successfully.', $count));

                return $this->resultRedirectFactory->create()
                    ->setPath(
                        '*/account/',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
            } else {
                $this->messageManager->addError($validate['msg']);
                return $this->resultRedirectFactory->create()
                    ->setPath(
                        '*/account/',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
            }
        }
        return $this->resultRedirectFactory->create()
                    ->setPath(
                        '*/account/',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
    }

    protected function saveData($completeDataObject)
    {
        try {
            $this->_timeSlotRepository->save($completeDataObject);
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    'Can not save the time slots'
                )
            );
        }
    }

    /**
     * Save minimum required time for seller
     *
     * @return [type] [description]
     */
    protected function saveMinimumOrderTime()
    {
        $customerData['minimum_time_required'] = $this->getRequest()->getParam('minimum_time_required');
        if ($customerData['minimum_time_required'] !== '' && is_numeric($customerData['minimum_time_required'])) {
            $customerId = $this->_customerSessionFactory->create()->getCustomerId();
            $savedCustomerData = $this->_customerRepository->getById($customerId);

            $customer = $this->_customerDataFactory->create();

            $customerData = array_merge(
                $this->_customerMapper->toFlatArray($savedCustomerData),
                $customerData
            );
            $customerData['id'] = $customerId;
            $this->_dataObjectHelper->populateWithArray(
                $customer,
                $customerData,
                CustomerInterface::class
            );
            $this->_customerRepository->save($customer);
        }
    }

    /**
     * Delete Slot
     *
     * @param int $id
     */
    protected function deleteDataById($id)
    {
        if ($id) {
            try {
                $this->_timeSlotRepository->deleteById($id);
            } catch (\Exception $e) {
                throw new LocalizedException(
                    __(
                        $e->getMessage()
                    )
                );
            }
        }
    }
    
    /**
     * Slot Validation
     *
     * @param  $data
     * @return bool
     */
    protected function validateData()
    {
        $error = ['error' => false, 'msg' => ''];

        $orderProcessTime = $this->getRequest()->getParam('minimum_time_required');
        if (empty($orderProcessTime) && !is_numeric($orderProcessTime)) {
            return $error = ['error' => true, 'msg' => 'Order Process Time is Required'];
        }
        
        $slotData = $this->getRequest()->getParam('timedelivery');
        if (!isset($slotData['slot'])) {
            return ['error' => true, 'msg' => 'No time slots available.'];
        }
        foreach ($slotData['slot'] as $key => $value) {
            if (!$value['is_delete']) {
                if ($value['order_count'] == '' || !is_numeric($value['order_count'])) {
                    $error = ['error' => true, 'msg' => 'Quotas must have numeric value.'];
                }
                if ($value['start_time'] == '' || !$value['start_time']) {
                    $error = ['error' => true, 'msg' => 'Start time field must be have valid value.'];
                }
                if ($value['end_time'] == '' || !$value['end_time']) {
                    $error = ['error' => true, 'msg' => 'End time field must be have valid value.'];
                }
                if (strtotime($value['end_time']) < strtotime($value['start_time'])) {
                    $error = ['error' => true, 'msg' => 'End time must be greater than start time.'];
                }
            }
        }
        return $error;
    }
}
