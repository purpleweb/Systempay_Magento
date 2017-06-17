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

class Lyra_Systempay_Model_Payment_Postfinance extends Lyra_Systempay_Model_Payment_Abstract
{
    protected $_code = 'systempay_postfinance';
    protected $_formBlockType = 'systempay/postfinance';

    protected  function _setExtraFields($order)
    {
        // override with PostFinance payment cards
        $this->_systempayRequest->set('payment_cards', 'POSTFINANCE;POSTFINANCE_EFIN');
    }

    /**
     * Assign data to info model instance
     *
     * @param mixed $data
     * @return Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        $info = $this->getInfoInstance();

        // init all payment data
        $info->setCcType(null)
                ->setCcLast4(null)
                ->setCcNumber(null)
                ->setCcCid(null)
                ->setCcExpMonth(null)
                ->setCcExpYear(null)
                ->setAdditionalData(null);

        return $this;
    }
}
