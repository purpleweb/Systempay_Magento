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

class Lyra_Systempay_Model_Field_Oney_Active extends Mage_Core_Model_Config_Data
{
    protected $message;

    public function save()
    {
        $this->message = '';

        if ($this->getValue() /* sub-module enabled */) {
            try {
                // check Oney requirements
                Mage::helper('systempay/util')->checkOneyRequirements($this->getScope(), $this->getScopeId());
            } catch (Mage_Core_Exception $e) {
                $this->setValue(0);

                $this->message = $e->getMessage();
            }
        }

        return parent::save();
    }

    public function afterCommitCallback()
    {
        if (!empty($this->message)) {
            Mage::throwException(
                $this->message . "\n" . Mage::helper('systempay')->__('FacilyPay Oney payment mean cannot be used.')
            );
        }

        return parent::afterCommitCallback();
    }
}
