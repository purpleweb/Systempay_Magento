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

class Lyra_Systempay_Block_Oneclick_Cart extends Mage_Core_Block_Template
{
    public function isOneclickAvailable()
    {
        $model = Mage::getModel('systempay/payment_standard');

        // 1-Click is not available in current context
        $configContext = $model->getConfigData('one_click_location');
        if ($configContext != Lyra_Systempay_Helper_Payment::ONECLICK_LOCATION_CART
                && $configContext != Lyra_Systempay_Helper_Payment::ONECLICK_LOCATION_BOTH) {
            return false;
        }

        return $model->isOneclickAvailable();
    }

    public function getConfigData($name)
    {
        return Mage::getModel('systempay/payment_standard')->getConfigData($name);
    }
}
