<?php

namespace Mpx\Marketplace\Observer\Product\Attribute;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Controller\Result\ForwardFactory;

class NoRoute implements ObserverInterface
{
    /**
     * @var ForwardFactory
     */
    protected $forwardFactory;

    /**
     * @param ForwardFactory $forwardFactory
     */
    public function __construct(
        ForwardFactory $forwardFactory
    ) {
        $this->forwardFactory = $forwardFactory;
    }

    /**
     * Forward page 404
     *
     * @param Observer $observer
     * @return \Magento\Framework\Controller\Result\Forward|void
     */
    public function execute(Observer $observer)
    {
        $resultForward = $this->forwardFactory->create();
        $resultForward->setController('index');
        $resultForward->forward('defaultNoRoute');
        return $resultForward;
    }
}
