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

abstract class Lyra_Systempay_Block_Abstract extends Mage_Payment_Block_Form
{
    protected $_model;

    protected function _construct()
    {
        parent::_construct();

        $logoURL = $this->_checkAndGetSkinUrl($this->getConfigData('module_logo'));

        if (!Mage::helper('systempay')->isAdmin() && $logoURL) {
            $logo = Mage::getConfig()->getBlockClassName('core/template');
            $logo = new $logo;
            $logo->setTemplate('systempay/logo.phtml');
            $logo->setLogoSrc($logoURL);
            $logo->setMethodTitle($this->getConfigData('title'));

            // add logo to the method title
            $this->setMethodLabelAfterHtml($logo->toHtml());
        }
    }

    protected function _checkAndGetSkinUrl($fileName)
    {
        if (!$fileName) {
            return false;
        }

        $filePath = Mage::getBaseDir('media') . DS . 'systempay' . DS . 'logos' . DS . $fileName;
        if (!file_exists($filePath)) {
            return false;
        }

        return Mage::getBaseUrl('media') . 'systempay/logos/' . $fileName;
    }

    public function getCcTypeImageSrc($card)
    {
        $card = strtolower($card);

        $path = Mage::getBaseDir('media') . DS . 'systempay' . DS . 'ct' . DS . $card . '.png';
        if (file_exists($path)) {
            return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'systempay/ct/' . $card . '.png';
        } else {
            return false;
        }
    }

    protected function _getModel()
    {
        return Mage::getModel('systempay/payment_' . $this->_model);
    }

    public function getConfigData($name)
    {
        return $this->_getModel()->getConfigData($name);
    }
}
