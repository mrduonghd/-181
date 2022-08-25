<?php

namespace Mpx\OrderComment\Controller\Comment;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class Add extends \Magento\Framework\App\Action\Action
{
    const DEFAULT_VALUE_NOTIFY = false;

    const DEFAULT_VALUE_VISIBLE = false;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var TimezoneInterface
     */
    protected $_timezoneInterface;

    /**
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param JsonFactory $resultJsonFactory
     * @param TimezoneInterface $timezoneInterface
     */
    public function __construct(
        Context $context,
        OrderRepositoryInterface $orderRepository,
        JsonFactory $resultJsonFactory,
        TimezoneInterface $timezoneInterface
    ) {
        $this->orderRepository = $orderRepository;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_timezoneInterface = $timezoneInterface;
        parent::__construct($context);
    }

    /**
     * Add order comment action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $response = ['error' => true, 'message' => __('Failed to add the comment.')];
        $orderId = $this->getRequest()->getParam('order_id');
        if (!$orderId) {
            $resultJson->setData($response);
            return $resultJson;
        }
        $order = $this->orderRepository->get($orderId);
        if ($order) {
            try {
                $data = $this->getRequest()->getParam('comment_content');
                if (empty($data)) {
                    $response = ['error' => true, 'message' => __('The comment is missing. Enter and try again.')];
                    $resultJson->setData($response);
                    return $resultJson;
                }
                $notify = self::DEFAULT_VALUE_NOTIFY;
                $visible = self::DEFAULT_VALUE_VISIBLE;

                $history = $order->addStatusHistoryComment($data);
                $history->setIsVisibleOnFront($visible);
                $history->setIsCustomerNotified($notify);
                $history->save();

                $result['comment_id'] = $history->getEntityId();
                $result['comment_content'] = $history->getComment();
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
        $resultJson->setData($response);
        return $resultJson;
    }
}
