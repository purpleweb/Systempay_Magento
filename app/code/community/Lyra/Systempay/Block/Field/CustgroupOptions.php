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
 * Custom renderer for the Systempay customer group options field.
 */
class Lyra_Systempay_Block_Field_CustgroupOptions extends Mage_Adminhtml_Block_System_Config_Form_Field_Array_Abstract
{
    public function __construct()
    {
        $this->addColumn(
            'title',
            array(
                'label' => Mage::helper('systempay')->__('Customer group'),
                'style' => 'width: 260px;',
                'renderer' => new Lyra_Systempay_Block_Field_Column_Label
            )
        );
        $this->addColumn(
            'amount_min',
            array(
                'label' => Mage::helper('systempay')->__('Minimum amount'),
                'style' => 'width: 210px;'
            )
        );
        $this->addColumn(
            'amount_max',
            array(
                'label' => Mage::helper('systempay')->__('Maximum amount'),
                'style' => 'width: 210px;'
            )
        );

        $this->_addAfter = false;

        parent::__construct();

        $this->setTemplate('systempay/field/array.phtml');
    }

    /**
     * Obtain existing data from form element.
     *
     * Each row will be instance of Varien_Object
     *
     * @return array
     */
    public function getArrayRows()
    {
        /** @var array[string][string] $groups */
        $groups = $this->_getAllCustomerGroups();

        $savedGroups = $this->getElement()->getValue();
        if (!is_array($savedGroups)) {
            $savedGroups = array();
        }

        if (!empty($savedGroups)) {
            foreach ($savedGroups as $id => $savedGroup) {
                if (key_exists($savedGroup['code'], $groups)) {
                    // refresh group title
                    $savedGroups[$id]['title'] = $groups[$savedGroup['code']];
                    if ($savedGroup['code'] === 'all') {
                        $savedGroups[$id]['color'] = '#e6e6e6';
                    }

                    unset($groups[$savedGroup['code']]);
                }
            }
        }

        // add not saved yet groups
        foreach ($groups as $code => $title) {
            $group = array(
                    'code' => $code,
                    'title' => $title,
                    'amount_min' => '',
                    'amount_max' => ''
            );

            if ($code === 'all') {
                // add all groups entry
                $group['color'] = '#e6e6e6';
                $savedGroups = array_merge(array(uniqid('_all_') => $group), $savedGroups);
            } else {
                $savedGroups[uniqid('_' . $code . '_')] = $group;
            }
        }

        $this->getElement()->setValue($savedGroups);
        return parent::getArrayRows();
    }

    protected function _getAllCustomerGroups()
    {
        $options = array();
        $options['all'] = Mage::helper('systempay')->__('ALL GROUPS');

        foreach (Mage::getModel('customer/group')->getCollection() as $group) {
            $options[$group->getCustomerGroupId()] = $group->getCustomerGroupCode();
        }

        return $options;
    }
}
