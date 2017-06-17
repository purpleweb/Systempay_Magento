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
 * Custom renderer for the Systempay section title fields.
 */
class Lyra_Systempay_Block_Field_SectionTitle extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Get element HTML code.
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '<div style="background-color: #D1DEDF; border: 1px solid #849BA3; height: 26px; padding-top: 5px;">';
        $html .= '<p style="font-size: 11px; margin: 0; padding-left: 20px;"><b>' . $element->getLabel() . '</b></p>';
        $html .= '</div>';

        return $html;
    }

    /**
     * Unset some non-related element parameters.
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '<tr style="height: 13px;"><td colspan="4"></td></tr>';
        $html .= '<tr>';
        $html .= '<td colspan="4">';
        $html .= $this->_getElementHtml($element);

        if ($element->getComment()) {
            $html.= '<p class="note" style="margin-left: 20px; margin-bottom: 15px;"><span>'
                .$element->getComment().'</span></p>';
        }

        $html .= '</td>';
        $html .= '</tr>';

        return $html;
    }
}
