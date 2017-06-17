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

abstract class Lyra_Systempay_Block_Oneclick_Shipping_Abstract extends Mage_Core_Block_Template
{
    protected $_checkout = null;
    protected $_quote = null;
    protected $_address = null;

    /**
     * Get Systempay 1-Click checkout session.
     *
     * @return Lyra_Systempay_Model_Session
     */
    public function getCheckout()
    {
        if (null === $this->_checkout) {
            $this->_checkout = Mage::getSingleton('systempay/session');
        }

        return $this->_checkout;
    }

    /**
     * Get Systempay 1-Click active quote.
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        if (null === $this->_quote) {
            $this->_quote = $this->getCheckout()->getQuote();
        }

        return $this->_quote;
    }

    /**
     * Get address model.
     *
     * @return Mage_Sales_Model_Quote_Address
     */
    public function getAddress()
    {
        if (null === $this->_address) {
            $this->_address = $this->getQuote()->getShippingAddress();
        }

        return $this->_address;
    }

    protected function _afterToHtml($html)
    {
        $this->_quote = null;
        $this->_address = null;

        return parent::_afterToHtml($html);
    }
}
