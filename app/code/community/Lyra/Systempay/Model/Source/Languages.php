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

class Lyra_Systempay_Model_Source_Languages
{
    public function toOptionArray()
    {
        $options =  array();

        foreach (Lyra_Systempay_Model_Api_Api::getSupportedLanguages() as $code => $name) {
            $options[] = array (
                    'value' => $code,
                    'label' => Mage::helper('systempay')->__($name)
            );
        }

        return $options;
    }
}
