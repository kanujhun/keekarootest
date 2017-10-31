<?php
/**
 *
 * ShipperHQ
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * Shipper HQ Shipping
 *
 * @category ShipperHQ
 * @package ShipperHQ_WS
 * @copyright Copyright (c) 2015 Zowta LLC (http://www.ShipperHQ.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @author ShipperHQ Team sales@shipperhq.com
 */
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace ShipperHQ\WS;

use \ShipperHQ\WS\Rate\Response\ResponseSummary;

/**
 * Class AbstractWebServiceResponse
 *
 * @package ShipperHQ\WS\Response
 */
abstract class AbstractWebServiceResponse implements WebServiceResponseInterface
{
    public $errors = [];
    public $responseSummary;

    /**
     * @param ResponseSummary $responseSummary
     * @param array $errors
     */
    function __construct(ResponseSummary $responseSummary = null, array $errors = [])
    {
        $this->responseSummary = $responseSummary;
        $this->errors = $errors;
    }

    /**
     * @param array $errors
     */
    public function setErrors(array $errors)
    {
        $this->errors = $errors;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param ResponseSummary $responseSummary
     */
    public function setResponseSummary(ResponseSummary $responseSummary)
    {
        $this->responseSummary = $responseSummary;
    }

    /**
     * @return ResponseSummary
     */
    public function getResponseSummary()
    {
        return $this->responseSummary;
    }


}
