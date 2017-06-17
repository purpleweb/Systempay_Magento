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

class Lyra_Systempay_Block_Gift extends Lyra_Systempay_Block_Abstract
{
    protected $_model = 'gift';

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('systempay/gift.phtml');
    }

    public function getAvailableGcTypes()
    {
        return $this->_getModel()->getAvailableGcTypes();
    }

    public function getGcTypeImageSrc($card)
    {
        $card = strtolower($card);

        $path = Mage::getBaseDir('media') . DS . 'systempay' . DS . 'gift' . DS . $card . '.png';
        if (file_exists($path)) {
            return Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'systempay/gift/' . $card . '.png';
        } else {
            return false;
        }
    }
}
