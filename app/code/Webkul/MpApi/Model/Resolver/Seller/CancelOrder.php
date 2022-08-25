<?php
declare(strict_types=1);

namespace Webkul\MpApi\Model\Resolver\Seller;

use Magento\Authorization\Model\UserContextInterface;
use Webkul\MpApi\Model\Seller\SellerManagement;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * Book field resolver, used for GraphQL request processing
 */
class CancelOrder implements ResolverInterface
{
    protected $sellerManagement;

    /**
     *
     * @param SellerManagement $sellerManagement
     */
    public function __construct(
        SellerManagement $sellerManagement
    ) {
        $this->sellerManagement = $sellerManagement;
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
        if ((!$context->getUserId()) || $context->getUserType() == UserContextInterface::USER_TYPE_GUEST) {
            throw new GraphQlAuthorizationException(
                __(
                    'Current customer does not have access to the resource "%1"',
                    [\Magento\Customer\Model\Customer::ENTITY]
                )
            );
        }
        if (!isset($args['orderid'])) {
            throw new GraphQlInputException(
                __("'orderid' input argument is required.")
            );
        }
        $result = $this->sellerManagement->cancelOrder($context->getUserId(), $args['orderid']);
        if ($result['item']['status'] == 2) {
            throw new GraphQlAuthorizationException(
                __(
                    $result['item']['message']
                )
            );
        }
        return $result['item'];
    }
}
