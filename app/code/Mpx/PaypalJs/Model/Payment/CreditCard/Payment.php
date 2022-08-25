<?php

namespace Mpx\PaypalJs\Model\Payment\CreditCard;

/**
 * class Payment
 * Credit Card
 */
class Payment extends \Mpx\PaypalJs\Model\Payment\PaypalJs\Payment
{
    const CODE = 'paypalcc';

    protected $_code = self::CODE;
}
