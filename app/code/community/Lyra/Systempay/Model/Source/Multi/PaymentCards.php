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

class Lyra_Systempay_Model_Source_Multi_PaymentCards
{
    private $_allMultiCards = array(
        'AMEX', 'CB', 'DINERS', 'DISCOVER', 'E-CARTEBLEUE', 'JCB', 'MASTERCARD',
        'PRV_BDP', 'PRV_BDT', 'PRV_OPT', 'PRV_SOC', 'VISA', 'VISA_ELECTRON'
    );

    public function toOptionArray()
    {
        $options = array();

        // add ALL value at the beginning
        $options[] = array('value' => '', 'label' => Mage::helper('systempay')->__('ALL'));

        foreach (Lyra_Systempay_Model_Api_Api::getSupportedCardTypes() as $code => $name) {
            if (!in_array($code, $this->_allMultiCards)) {
                continue;
            }

            $options[] = array('value' => $code, 'label' => $name);
        }

        return $options;
    }

    public function getMultiCards()
    {
        $options =  array();

        foreach (Lyra_Systempay_Model_Api_Api::getSupportedCardTypes() as $code => $name) {
            if (!in_array($code, $this->_allMultiCards)) {
                continue;
            }

            $options[$code] = $name;
        }

        return $options;
    }
}
