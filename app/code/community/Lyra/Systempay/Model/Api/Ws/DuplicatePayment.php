<?php
/**
 * Systempay V2-Payment Module version 1.7.1 for Magento 1.4-1.9. Support contact : supportvad@lyra-network.com.
 *
 * NOTICE OF LICENSE
 *
 * This source file is licensed under the Open Software License version 3.0
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category  payment
 * @package   systempay
 * @author    Lyra Network (http://www.lyra-network.com/)
 * @copyright 2014-2017 Lyra Network and contributors
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Lyra\Systempay\Model\Api\Ws;

class DuplicatePayment
{
    /**
     * @var CommonRequest $commonRequest
     */
    private $commonRequest = null;

    /**
     * @var PaymentRequest $paymentRequest
     */
    private $paymentRequest = null;

    /**
     * @var OrderRequest $orderRequest
     */
    private $orderRequest = null;

    /**
     * @var QueryRequest $queryRequest
     */
    private $queryRequest = null;

    /**
     * @return CommonRequest
     */
    public function getCommonRequest()
    {
        return $this->commonRequest;
    }

    /**
     * @param CommonRequest $commonRequest
     * @return DuplicatePayment
     */
    public function setCommonRequest($commonRequest)
    {
        $this->commonRequest = $commonRequest;
        return $this;
    }

    /**
     * @return PaymentRequest
     */
    public function getPaymentRequest()
    {
        return $this->paymentRequest;
    }

    /**
     * @param PaymentRequest $paymentRequest
     * @return DuplicatePayment
     */
    public function setPaymentRequest($paymentRequest)
    {
        $this->paymentRequest = $paymentRequest;
        return $this;
    }

    /**
     * @return OrderRequest
     */
    public function getOrderRequest()
    {
        return $this->orderRequest;
    }

    /**
     * @param OrderRequest $orderRequest
     * @return DuplicatePayment
     */
    public function setOrderRequest($orderRequest)
    {
        $this->orderRequest = $orderRequest;
        return $this;
    }

    /**
     * @return QueryRequest
     */
    public function getQueryRequest()
    {
        return $this->queryRequest;
    }

    /**
     * @param QueryRequest $queryRequest
     * @return DuplicatePayment
     */
    public function setQueryRequest($queryRequest)
    {
        $this->queryRequest = $queryRequest;
        return $this;
    }
}
