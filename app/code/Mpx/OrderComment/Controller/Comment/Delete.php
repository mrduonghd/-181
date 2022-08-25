<?php

namespace Mpx\OrderComment\Controller\Comment;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\Order\Status\HistoryFactory;
use Magento\Sales\Model\ResourceModel\Order\Status\History as OrderStatusHistoryResource;

class Delete extends \Magento\Framework\App\Action\Action
{
    const STATUS_DELETE_ORDER_COMMENT = 1;

    /**
     * @var HistoryFactory
     */
    protected $_orderHistoryFactory;

    /**
     * @var OrderStatusHistoryResource
     */
    protected $orderStatusHistoryResource;

    /**
     * @var TimezoneInterface
     */
    protected $_timezoneInterface;

    /**
     * @param Context $context
     * @param HistoryFactory $orderHistoryFactory
     * @param OrderStatusHistoryResource $orderStatusHistoryResource
     * @param JsonFactory $resultJsonFactory
     * @param TimezoneInterface $timezoneInterface
     */
    public function __construct(
        Context $context,
        HistoryFactory $orderHistoryFactory,
        OrderStatusHistoryResource $orderStatusHistoryResource,
        JsonFactory $resultJsonFactory,
        TimezoneInterface $timezoneInterface
    ) {
        $this->_orderHistoryFactory = $orderHistoryFactory;
        $this->orderStatusHistoryResource = $orderStatusHistoryResource;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_timezoneInterface = $timezoneInterface;
        parent::__construct($context);
    }

    /**
     * Delele order comment
     *
     * @return \Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $response = ['error' => true, 'message' => __('Failed to delete the comment.')];
        $commentId = $this->getRequest()->getParam('comment_id');
        if (!$commentId) {
            $resultJson->setData($response);
            return $resultJson;
        }
        try {
            $history = $this->_orderHistoryFactory->create()->load($commentId);
            $history->setData('comment_status', self::STATUS_DELETE_ORDER_COMMENT);
            $this->orderStatusHistoryResource->save($history);

            $result['comment_id'] = $commentId;
            $result['date'] = $this->_timezoneInterface
                ->date(new \DateTime($history->getCreatedAt()))
                ->format('Y/m/d');
            $result['time'] = $this->_timezoneInterface
                ->formatDateTime(
                    new \DateTime($history->getCreatedAt()),
                    \IntlDateFormatter::NONE,
                    \IntlDateFormatter::MEDIUM
                );

            $resultJson->setData($result);
            return $resultJson;
        } catch (\Exception $e) {
            $resultJson->setData($response);
            return $resultJson;
        }
    }
}
