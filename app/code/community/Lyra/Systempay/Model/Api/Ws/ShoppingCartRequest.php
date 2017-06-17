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

class ShoppingCartRequest
{
    /**
     * @var int $insuranceAmount
     */
    private $insuranceAmount = null;

    /**
     * @var int $shippingAmount
     */
    private $shippingAmount = null;

    /**
     * @var int $taxAmount
     */
    private $taxAmount = null;

    /**
     * @var CartItemInfo[] $cartItemInfo
     */
    private $cartItemInfo = null;

    /**
     * @return int
     */
    public function getInsuranceAmount()
    {
        return $this->insuranceAmount;
    }

    /**
     * @param int $insuranceAmount
     * @return ShoppingCartRequest
     */
    public function setInsuranceAmount($insuranceAmount)
    {
        $this->insuranceAmount = $insuranceAmount;
        return $this;
    }

    /**
     * @return int
     */
    public function getShippingAmount()
    {
        return $this->shippingAmount;
    }

    /**
     * @param int $shippingAmount
     * @return ShoppingCartRequest
     */
    public function setShippingAmount($shippingAmount)
    {
        $this->shippingAmount = $shippingAmount;
        return $this;
    }

    /**
     * @return int
     */
    public function getTaxAmount()
    {
        return $this->taxAmount;
    }

    /**
     * @param int $taxAmount
     * @return ShoppingCartRequest
     */
    public function setTaxAmount($taxAmount)
    {
        $this->taxAmount = $taxAmount;
        return $this;
    }

    /**
     * @return CartItemInfo[]
     */
    public function getCartItemInfo()
    {
        return $this->cartItemInfo;
    }

    /**
     * @param CartItemInfo[] $cartItemInfo
     * @return ShoppingCartRequest
     */
    public function setCartItemInfo(array $cartItemInfo)
    {
        $this->cartItemInfo = $cartItemInfo;
        return $this;
    }
}
