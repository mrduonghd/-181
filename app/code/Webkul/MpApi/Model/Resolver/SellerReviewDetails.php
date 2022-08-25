<?php
declare(strict_types=1);

namespace Webkul\MpApi\Model\Resolver;

use Webkul\Marketplace\Model\FeedbackFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Book field resolver, used for GraphQL request processing
 */
class SellerReviewDetails implements ResolverInterface
{
    protected $reviewCollection;

    /**
     *
     * @param SellerManagement $sellerManagement
     */
    public function __construct(
        FeedbackFactory $reviewCollection
    ) {
        $this->reviewCollection = $reviewCollection;
    }
    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($args['id'])) {
            throw new GraphQlInputException(
                __("'id' input argument is required.")
            );
        }
        $returnArray = [];
        $reviewCollection = $this->reviewCollection->create()
            ->getCollection()
            ->addFieldToFilter('status', ['neq' => 0])
            ->addFieldToFilter('entity_id', $args['id']);
        if ($reviewCollection->getSize() == 0) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('not a valid review id')
            );
        }
        return $reviewCollection->toArray();
    }
}
