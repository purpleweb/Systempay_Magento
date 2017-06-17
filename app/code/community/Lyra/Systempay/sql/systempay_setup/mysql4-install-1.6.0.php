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

$installFile = dirname(__FILE__) . DS . 'install-1.6.0.php';
if (file_exists($installFile)) {
    require_once $installFile;
}

/**
 * For backward compatibility (less than 1.6 Magento versions).
 */
if (version_compare(Mage::getVersion(), '1.6.0.0', '<')) {
    /** @var $this Lyra_Systempay_Model_Resource_Setup */
    $installer = $this;
    $installer->startSetup();

    $connection = $installer->getConnection();

    $statusTable = $installer->getTable('sales_order_status');
    $stateTable = $installer->getTable('sales_order_status_state');

    if ($installer->tableExists($statusTable) && $installer->tableExists($stateTable)) {
        $select = $connection->select()->from($statusTable, 'status')->where('status = "systempay_to_validate"');
        if (!$connection->fetchOne($select)) { // status does not exist
            $connection->insert(
                    $statusTable,
                    array('status' => 'systempay_to_validate', 'label' => 'To validate payment')
            );

            $connection->insert(
                    $stateTable,
                    array('status' => 'systempay_to_validate', 'state' => 'payment_review', 'is_default' => 0)
            );
        }
    }

    $installer->endSetup();
}
