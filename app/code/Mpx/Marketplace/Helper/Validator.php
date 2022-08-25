<?php
/**
 * Mpx Software.
 *
 * @category  Mpx
 * @package   Mpx_Marketplace
 * @author    Mpx
 */

namespace Mpx\Marketplace\Helper;

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Helper define validate function
 */
class Validator extends AbstractHelper
{
    /**
     * Check if number is decimal
     *
     * @param string $val
     * @return bool
     */
    public function isDecimal(string $val): bool
    {
        return is_numeric($val) && floor($val) != $val;
    }

    /**
     * Validate time with matching input format
     *
     * @param string $dateTime
     * @param string $format
     * @return bool
     */
    public function validateTimeFormat(string $dateTime, string $format = ''): bool
    {
        if (!$format) {
            return false;
        }
        $validator = new \Zend_Validate_Date($format);
        if ($validator->isValid($dateTime)) {
            return true;
        }
        return false;
    }
}
