<?php
declare(strict_types=1);

namespace Webkul\MpApi\Model\Resolver;

use Webkul\Marketplace\Model\FeedbackFactory;
use Webkul\Marketplace\Model\SellerFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Book field resolver, used for GraphQL request processing
 */
class SellerReviews implements ResolverInterface
{
    protected $reviewCollection;

    protected $sellerCollection;

    /**
     *
     * @param SellerManagement $sellerManagement
     */
    public function __construct(
        FeedbackFactory $reviewCollection,
        SellerFactory $sellerCollection
    ) {
        $this->reviewCollection = $reviewCollection;
        $this->sellerCollection = $sellerCollection;
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
        if (!$this->isSeller($args['id'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('invalid seller')
            );
        }
        $returnArray = [];
        $reviewCollection = $this->reviewCollection->create()
            ->getCollection()
            ->addFieldToFilter('status', ['neq' => 0])
            ->addFieldToFilter('seller_id', $args['id']);
        if ($reviewCollection->getSize() == 0) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('no reviews yet on this seller')
            );
        }
        return $reviewCollection->toArray();
    }

    /**
     * Return the Customer seller status.
     *
     * @return bool|0|1
     */
    public function isSeller($id)
    {
        $sellerStatus = 0;
        $model = $this->sellerCollection->create()
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                $id
            );
        foreach ($model as $value) {
            $sellerStatus = $value->getIsSeller();
        }

        return $sellerStatus;
    }
}
