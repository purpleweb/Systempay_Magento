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
 * Custom renderer for the Systempay init button.
 */
class Lyra_Systempay_Block_Field_InitButton extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * Set template to itself.
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $this->setTemplate('systempay/field/init_button.phtml');

        return $this;
    }

    /**
     * Unset some non-related element parameters.
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    public function render(Varien_Data_Form_Element_Abstract $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Get the button and scripts contents.
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $fieldConfig = $element->getFieldConfig();

        $this->addData(
            array(
                'button_label' => Mage::helper('systempay')->__((string)$fieldConfig->button_label),
                'button_url'   => $this->getUrl($fieldConfig->button_url),
                'html_id' => $element->getHtmlId()
            )
        );

        return $this->_toHtml();
    }
}
