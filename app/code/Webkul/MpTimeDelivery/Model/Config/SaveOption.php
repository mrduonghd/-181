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
namespace Webkul\MpTimeDelivery\Model\Config;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterfaceFactory;
use Webkul\MpTimeDelivery\Api\TimeslotConfigRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterface;

class SaveOption extends Value
{
    /**
     * @var \Webkul\MpTimeDelivery\Api\TimeslotConfigRepositoryInterface
     */
    protected $timeSlotRepository;

    /**
     * @var \Webkul\MpTimeDelivery\Api\Data\TimeslotConfigInterfaceFactory
     */
    protected $timeSlotDataFactory;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;
    
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * @param Context                           $context
     * @param Registry                          $registry
     * @param ScopeConfigInterface              $config
     * @param TypeListInterface                 $cacheTypeList
     * @param ValueFactory                      $configValueFactory
     * @param AbstractResource                  $resource
     * @param AbstractDb                        $resourceCollection
     * @param DateTime                          $dateTime
     * @param TimeslotConfigRepositoryInterface $timeSlotRepository
     * @param TimeslotConfigInterfaceFactory    $timeSlotDataFactory
     * @param DataObjectHelper                  $dataObjectHelper
     * @param string                            $runModelPath
     * @param array                             $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        DateTime $dateTime,
        TimeslotConfigRepositoryInterface $timeSlotRepository,
        TimeslotConfigInterfaceFactory $timeSlotDataFactory,
        DataObjectHelper $dataObjectHelper,
        array $data = []
    ) {
        $this->timeSlotRepository = $timeSlotRepository;
        $this->timeSlotDataFactory = $timeSlotDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dateTime = $dateTime;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     * @throws \Exception
     */
    public function afterSave()
    {
        $timeSlotData = $this->getData('groups/config/fields/slots_data/timedelivery');

        $error = $this->validateData($timeSlotData);
        if (!$error && !empty($timeSlotData['slot'])) {
            $count = 0;
            foreach ($timeSlotData['slot'] as $data) {
                $data['start_time'] =  $this->dateTime->gmtDate('H:i', $data['start_time']);
                $data['end_time'] =  $this->dateTime->gmtDate('H:i', $data['end_time']);
                if (isset($data['is_delete']) && $data['is_delete']) {
                    $this->deleteDataById($data['entity_id']);
                    continue;
                }
                $completeDataObject = $this->timeSlotDataFactory->create();

                $this->dataObjectHelper->populateWithArray(
                    $completeDataObject,
                    $data,
                    TimeslotConfigInterface::class
                );
                $this->saveData($completeDataObject);
            }
        }

        return parent::afterSave();
    }

    /**
     * Save Slot Data
     *
     * @param object $completeDataObject
     */
    public function saveData($completeDataObject)
    {
        try {
            $this->timeSlotRepository->save($completeDataObject);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception('Can not save the records.');
        }
    }

    /**
     * Delete Slot by ID
     *
     * @param int $id
     */
    public function deleteDataById($id)
    {
        try {
            $this->timeSlotRepository->deleteById($id);
        } catch (\Exception $e) {
            throw new LocalizedException(
                __(
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Validate Data
     *
     * @param array $data
     */
    public function validateData($data)
    {
        $error = false;
        if (!isset($data['slot'])) {
            return $error;
        }
        foreach ($data['slot'] as $key => $value) {
            if ($value['order_count'] == '' || !is_numeric($value['order_count'])) {
                $error = true;
            }
            if ($value['start_time'] == '' || !$value['start_time']) {
                $error = true;
            }
            if ($value['end_time'] == '' || !$value['end_time']) {
                $error = true;
            }
        }
        return $error;
    }
}
