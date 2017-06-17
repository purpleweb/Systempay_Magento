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

/**
 * Custom renderer for the Systempay multi payment options field
 */
class Lyra_Systempay_Block_Field_Multi_PaymentOptions extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn(
            'label',
            array(
                'label' => Mage::helper('systempay')->__('Label'),
                'style' => 'width: 150px;',
            )
        );
        $this->addColumn(
            'minimum',
            array(
                'label' => Mage::helper('systempay')->__('Min. amount'),
                'style' => 'width: 80px;',
            )
        );
        $this->addColumn(
            'maximum',
            array(
                'label' => Mage::helper('systempay')->__('Max. amount'),
                'style' => 'width: 80px;',
            )
        );

        $cards = Lyra_Systempay_Model_Api_Api::getSupportedCardTypes();
        if (isset($cards['CB'])) {
            // if CB is available, we allow contract override.
            $this->addColumn(
                'contract',
                array(
                    'label' => Mage::helper('systempay')->__('Contract'),
                    'style' => 'width: 65px;',
                )
            );
        }

        $this->addColumn(
            'count',
            array(
                'label' => Mage::helper('systempay')->__('Count'),
                'style' => 'width: 65px;',
            )
        );
        $this->addColumn(
            'period',
            array(
                'label' => Mage::helper('systempay')->__('Period'),
                'style' => 'width: 65px;',
            )
        );
        $this->addColumn(
            'first',
            array(
                'label' => Mage::helper('systempay')->__('1st payment'),
                'style' => 'width: 70px;',
            )
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('systempay')->__('Add');
        parent::__construct();
    }
}
