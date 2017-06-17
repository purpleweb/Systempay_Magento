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
 * Custom renderer for the Systempay category mapping field.
 */
class Lyra_Systempay_Block_Field_CategoryMapping extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn(
            'magento_category',
            array(
                'label' => Mage::helper('systempay')->__('Magento category'),
                'style' => 'width: 200px;',
                'renderer' => new Lyra_Systempay_Block_Field_Column_Label
            )
        );

        $options = array('options' => Mage::helper('systempay')->getConfigArray('product_categories'));;
        $this->addColumn(
            'systempay_category',
            array(
                'label' => Mage::helper('systempay')->__('Systempay category'),
                'style' => 'width: 200px;',
                'renderer' => new Lyra_Systempay_Block_Field_Column_List($options)
            )
        );

        $this->_addAfter = false;

        parent::__construct();

        $this->setTemplate('systempay/field/array.phtml');
    }

    /**
     * Obtain existing data from form element
     *
     * Each row will be instance of Varien_Object
     *
     * @return array
     */
    public function getArrayRows()
    {
        $value = array();

        /** @var array[string][string] $categories */
        $categories = $this->_getAllCategories();

        $savedCategories = $this->getElement()->getValue();
        if ($savedCategories && is_array($savedCategories) && !empty($savedCategories)) {
            foreach ($savedCategories as $id => $category) {
                if (key_exists($category['code'], $categories)) {
                    // add category current name
                    $category['magento_category'] = $categories[$category['code']];
                    $value[$id] = $category;

                    unset($categories[$category['code']]);
                }
            }
        }

        // add not saved yet categories
        if ($categories && is_array($categories) && !empty($categories)) {
            foreach ($categories as $code => $name) {
                $value[uniqid('_' . $code . '_')] = array(
                        'code' => $code,
                        'magento_category' => $name,
                        'systempay_category' => 'FOOD_AND_GROCERY',
                        'mark' => '*'
                );
            }
        }

        $this->getElement()->setValue($value);
        return parent::getArrayRows();
    }

    protected function _getAllCategories()
    {
        $options = array();

        $categories = Mage::getModel('catalog/category')->getCollection()
                    ->addAttributeToSelect('name')
                    ->addAttributeToSelect('id')
                    ->addIsActiveFilter();

        $storeId = Mage::getSingleton('adminhtml/config_data')->getStore();
        $rootId = $storeId ? Mage::app()->getStore($storeId)->getRootCategoryId() : null;
        if ($rootId) {
            $categories = $categories->addPathFilter("^1/$rootId/[0-9]+$");
        } else {
            $categories = $categories->addPathFilter("^1/[0-9]+/[0-9]+$");
        }

        foreach ($categories as $category) {
            $options[$category->getId()] = $category->getName();
        }

        return $options;
    }

    /**
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        $script = '<script type="text/javascript">
                //<![CDATA[
                document.observe("dom:loaded", function() {
                    $$("select.systempay_list_systempay_category").each(function(elt) {
                        var value = elt.readAttribute("currentvalue");

                        // option to select
                        var opt = elt.select("option[value=\"" + value + "\"]");
                        if (opt && opt.length > 0) {
                            opt[0].selected = true;
                        }
                    });';

        if ($this->getElement()->getCanUseWebsiteValue() || $this->getElement()->getCanUseDefaultValue()) {
            $script .= '
                    Event.observe($("payment_systempay_common_category"), "change", function() {
                        toggleValueElements(
                            $("payment_systempay_category_mapping_inherit"),
                            $("payment_systempay_category_mapping").parentNode
                        );
                    });';
        }

        $script .= '
                });
                //]]>
                </script>';

        return parent::_toHtml() . "\n" . $script;
    }
}
