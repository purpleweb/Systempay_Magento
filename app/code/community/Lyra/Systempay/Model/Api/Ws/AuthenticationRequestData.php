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

class AuthenticationRequestData
{
    /**
     * @var string $threeDSAcctId
     */
    private $threeDSAcctId = null;

    /**
     * @var string $threeDSAcsUrl
     */
    private $threeDSAcsUrl = null;

    /**
     * @var string $threeDSBrand
     */
    private $threeDSBrand = null;

    /**
     * @var string $threeDSEncodedPareq
     */
    private $threeDSEncodedPareq = null;

    /**
     * @var string $threeDSEnrolled
     */
    private $threeDSEnrolled = null;

    /**
     * @var string $threeDSRequestId
     */
    private $threeDSRequestId = null;

    /**
     * @return string
     */
    public function getThreeDSAcctId()
    {
        return $this->threeDSAcctId;
    }

    /**
     * @param string $threeDSAcctId
     * @return AuthenticationRequestData
     */
    public function setThreeDSAcctId($threeDSAcctId)
    {
        $this->threeDSAcctId = $threeDSAcctId;
        return $this;
    }

    /**
     * @return string
     */
    public function getThreeDSAcsUrl()
    {
        return $this->threeDSAcsUrl;
    }

    /**
     * @param string $threeDSAcsUrl
     * @return AuthenticationRequestData
     */
    public function setThreeDSAcsUrl($threeDSAcsUrl)
    {
        $this->threeDSAcsUrl = $threeDSAcsUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getThreeDSBrand()
    {
        return $this->threeDSBrand;
    }

    /**
     * @param string $threeDSBrand
     * @return AuthenticationRequestData
     */
    public function setThreeDSBrand($threeDSBrand)
    {
        $this->threeDSBrand = $threeDSBrand;
        return $this;
    }

    /**
     * @return string
     */
    public function getThreeDSEncodedPareq()
    {
        return $this->threeDSEncodedPareq;
    }

    /**
     * @param string $threeDSEncodedPareq
     * @return AuthenticationRequestData
     */
    public function setThreeDSEncodedPareq($threeDSEncodedPareq)
    {
        $this->threeDSEncodedPareq = $threeDSEncodedPareq;
        return $this;
    }

    /**
     * @return string
     */
    public function getThreeDSEnrolled()
    {
        return $this->threeDSEnrolled;
    }

    /**
     * @param string $threeDSEnrolled
     * @return AuthenticationRequestData
     */
    public function setThreeDSEnrolled($threeDSEnrolled)
    {
        $this->threeDSEnrolled = $threeDSEnrolled;
        return $this;
    }

    /**
     * @return string
     */
    public function getThreeDSRequestId()
    {
        return $this->threeDSRequestId;
    }

    /**
     * @param string $threeDSRequestId
     * @return AuthenticationRequestData
     */
    public function setThreeDSRequestId($threeDSRequestId)
    {
        $this->threeDSRequestId = $threeDSRequestId;
        return $this;
    }
}
