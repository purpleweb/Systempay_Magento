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
 * Custom renderer for FacilyPay Oney payment options field.
 */
class Lyra_Systempay_Block_Field_Oney_PaymentOptions extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
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
            'code',
            array(
                'label' => Mage::helper('systempay')->__('Code'),
                'style' => 'width: 100px;'
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
        $this->addColumn(
            'count',
            array(
                'label' => Mage::helper('systempay')->__('Count'),
                'style' => 'width: 65px;',
            )
        );
        $this->addColumn(
            'rate',
            array(
                'label' => Mage::helper('systempay')->__('Rate'),
                'style' => 'width: 65px;',
            )
        );

        $this->_addAfter = false;
        $this->_addButtonLabel = Mage::helper('systempay')->__('Add');

        parent::__construct();
    }

    /**
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $script = '';

        if ($this->getElement()->getCanUseWebsiteValue() || $this->getElement()->getCanUseDefaultValue()) {
            $script .= '
                <script type="text/javascript">
                //<![CDATA[
                    document.observe("dom:loaded", function() {';

            if ($this->getElement()->getDisabled()) {
                $script .= '
                        toggleValueElements({checked: true}, $("payment_systempay_oney_payment_options").parentNode);';
            }

            $script .= '
                        Event.observe($("payment_systempay_oney_enable_payment_options"), "change", function() {
                            toggleValueElements(
                                $("payment_systempay_oney_payment_options_inherit"),
                                $("payment_systempay_oney_payment_options").parentNode
                            );
                        });
                    });
                //]]>
                </script>';
        }

        return '<div id="' . $this->getElement()->getId() . '">' . parent::_toHtml() . "\n" . $script . '</div>';
    }
}
