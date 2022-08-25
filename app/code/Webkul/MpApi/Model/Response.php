<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpApi
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpApi\Model;

class Response extends \Magento\Framework\DataObject implements \Webkul\MpApi\Api\Data\ResponseInterface
{

    /**
     * prepare api response .
     *
     * @return \Webkul\MpApi\Api\Data\ResponseInterface
     */
    public function getResponse()
    {
        $data = $this->_data;
        return $data;
    }
}
