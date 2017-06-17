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

class Lyra_Systempay_Model_Field_Oney_CustgroupOptions extends Lyra_Systempay_Model_Field_CustgroupOptions
{
    protected function _beforeSave()
    {
        $values = $this->getValue();

        $data = $this->getGroups('systempay_oney'); // get data of FacilyPay Oney config group
        if ($data['fields']['active']['value']) { // FacilyPay Oney is activated
            foreach ($values as $key => $value) {
                if (empty($value) || ($value['code'] !== 'all')) {
                    continue;
                }

                if (empty($value['amount_min'])) {
                    $field = 'Minimum amount';
                } elseif (empty($value['amount_max'])) {
                    $field = 'Maximum amount';
                }

                if (isset($field)) {
                    $field = Mage::helper('systempay')->__($field); // translate field name
                    $group = Mage::helper('systempay')->getConfigGroupTitle($this->getGroupId());
                    $msg = Mage::helper('systempay')->__('Please enter a value for &laquo;ALL GROUPS - %s&raquo; in &laquo;%s&raquo; section as agreed with Banque Accord.', $field, $group);

                    // throw exception
                    Mage::throwException($msg);
                }
            }
        }

        return parent::_beforeSave();
    }
}
