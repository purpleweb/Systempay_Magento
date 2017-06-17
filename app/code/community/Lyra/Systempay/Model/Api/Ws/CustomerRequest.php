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

class CustomerRequest
{
    /**
     * @var BillingDetailsRequest $billingDetails
     */
    private $billingDetails = null;

    /**
     * @var ShippingDetailsRequest $shippingDetails
     */
    private $shippingDetails = null;

    /**
     * @var ExtraDetailsRequest $extraDetails
     */
    private $extraDetails = null;

    /**
     * @return BillingDetailsRequest
     */
    public function getBillingDetails()
    {
        return $this->billingDetails;
    }

    /**
     * @param BillingDetailsRequest $billingDetails
     * @return CustomerRequest
     */
    public function setBillingDetails($billingDetails)
    {
        $this->billingDetails = $billingDetails;
        return $this;
    }

    /**
     * @return ShippingDetailsRequest
     */
    public function getShippingDetails()
    {
        return $this->shippingDetails;
    }

    /**
     * @param ShippingDetailsRequest $shippingDetails
     * @return CustomerRequest
     */
    public function setShippingDetails($shippingDetails)
    {
        $this->shippingDetails = $shippingDetails;
        return $this;
    }

    /**
     * @return ExtraDetailsRequest
     */
    public function getExtraDetails()
    {
        return $this->extraDetails;
    }

    /**
     * @param ExtraDetailsRequest $extraDetails
     * @return CustomerRequest
     */
    public function setExtraDetails($extraDetails)
    {
        $this->extraDetails = $extraDetails;
        return $this;
    }
}
