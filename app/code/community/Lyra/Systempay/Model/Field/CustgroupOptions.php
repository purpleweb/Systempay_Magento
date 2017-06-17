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

class Lyra_Systempay_Model_Field_CustgroupOptions extends Lyra_Systempay_Model_Field_Array
{
    protected $_eventPrefix = 'systempay_field_custgroup_options';

    protected function _beforeSave()
    {
        $values = $this->getValue();

        if (!is_array($values) || empty($values)) {
            $this->setValue(array());
        } else {
            $i = 0;
            foreach ($values as $key => $value) {
                $i++;

                if (empty($value)) {
                    continue;
                }

                if (!empty($value['amount_min']) && (!is_numeric($value['amount_min']) || $value['amount_min'] < 0)) {
                    $this->_throwError('Minimum amount', $i);
                } elseif (!empty($value['amount_max']) && (!is_numeric($value['amount_max']) || $value['amount_max'] < 0)) {
                    $this->_throwError('Maximum amount', $i);
                }
            }
        }

        return parent::_beforeSave();
    }
}
