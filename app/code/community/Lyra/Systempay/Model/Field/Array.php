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

class Lyra_Systempay_Model_Field_Array extends Mage_Adminhtml_Model_System_Config_Backend_Serialized_Array
{
    protected function _throwError($column, $position, $extraMsg = '')
    {
        // translate field and column names
        $field = Mage::helper('systempay')->__((string)$this->getFieldConfig()->label);
        $column = Mage::helper('systempay')->__((string)$column);
        $group = Mage::helper('systempay')->getConfigGroupTitle($this->getGroupId());

        // main message
        $msg = Mage::helper('systempay')->__('The field &laquo;%s&raquo; is invalid: please check column &laquo;%s&raquo; of the option %s in section &laquo;%s&raquo;.', $field, $column, $position, $group);

        if ($extraMsg) {
            $msg .= "\n" . Mage::helper('systempay')->__($extraMsg);
        }

        // throw exception
        Mage::throwException($msg);
    }
}
