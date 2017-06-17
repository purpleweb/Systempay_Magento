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
 * Custom renderer for the Systempay add gift cards field
 */
class Lyra_Systempay_Block_Field_Gift_AddedCards extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn(
            'code',
            array(
                'label' => Mage::helper('systempay')->__('Card code'),
                'style' => 'width: 100px;'
            )
        );
        $this->addColumn(
            'name',
            array(
                'label' => Mage::helper('systempay')->__('Card label'),
                'style' => 'width: 180px;'
            )
        );
        $this->addColumn(
            'logo',
            array(
                'label' => Mage::helper('systempay')->__('Card logo'),
                'style' => 'width: 340px;',
                'size' => '20',
                'renderer' => new Lyra_Systempay_Block_Field_Gift_UploadButton
            )
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('systempay')->__('Add');

        parent::__construct();
    }
}
