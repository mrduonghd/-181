<?php
/**
 * Mpx Software.
 *
 * @category  Mpx
 * @package   Mpx_Mpshipping
 * @author    Mpx
 */

namespace Mpx\Mpshipping\Helper;

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
}
