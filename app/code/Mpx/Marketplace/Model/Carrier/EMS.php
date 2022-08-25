<?php

namespace Mpx\Marketplace\Model\Carrier;

use Mpx\Marketplace\Model\Carrier\AbstractCarrier;

/**
 * EMS shipping
 */
class EMS extends AbstractCarrier
{
    /**
     * @var string
     */
    protected $_code = 'ems';
}
