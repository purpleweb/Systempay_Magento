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

class Lyra_Systempay_Model_Observer
{
    public function doPaymentRedirect($observer)
    {
        if (!$this->_getHelper()->isAdmin()) {
            // not an admin-passed order, do nothing
            return;
        }

        $order = $observer->getOrder();

        if (!$order || $order->getId() <= 0) {
            // order creation failed
            return;
        }

        $method = $order->getPayment()->getMethodInstance();

        if ($method instanceof Lyra_Systempay_Model_Payment_Standard && $this->_getHelper()->isCurrentlySecure()) {
            // use WS instead
            return;
        }

        if ($method instanceof Lyra_Systempay_Model_Payment_Abstract) {
            // backend payment with redirection

            $flag = false;
            if ($data = Mage::app()->getRequest()->getPost('order')) {
                $flag = isset($data['send_confirmation']) ? (bool)$data['send_confirmation'] : false;
            }

            $session = Mage::getSingleton('adminhtml/session_quote');

            $session->setSystempayCanSendNewEmail($flag); // flag that allows sending new order email

            $session->setQuoteId($observer->getQuote()->getId())
                    ->setLastSuccessQuoteId($observer->getQuote()->getId())
                    ->setLastRealOrderId($order->getIncrementId());

            session_write_close();

            $redirectUrl = $this->_getHelper()->prepareUrl('adminhtml/systempay_payment/form', 0, true);
            Mage::app()->getResponse()->setRedirect($redirectUrl)->sendHeadersAndExit();
        }
    }

    public function doPaymentMultiUpdate($observer)
    {
        $payment = $observer->getDataObject();

        if ($payment->getMethod() != 'systempay_multi') {
            // not systempay multiple payment, do nothing
            return;
        }

        // retreive selected option
        $option = @unserialize($payment->getAdditionalData());
        if (isset($option) && is_array($option)) {
            $payment->setMethod('systempay_multi_' . $option['count'] . 'x');
        }
    }

    public function doPaymentMethodColumnAppend($observer)
    {
        /* @var $block Mage_Adminhtml_Block_Sales_Order_Grid */
        $block = $observer->getBlock();

        if (isset($block) && ($block->getType() == 'adminhtml/sales_order_grid')) {
            $availableMethods = Mage::getStoreConfig('payment');

            if (!$block->getColumn('payment_method')) {
                $groupedMethods = array();
                $methods = array();

                foreach ($availableMethods as $code => $method) {
                    if (!is_array($method) || !isset($method['model'])) {
                        continue;
                    }

                    // use method codes and titles only
                    $title = $code;
                    if (isset($method['title']) && !empty($method['title'])) {
                        $title = Mage::helper('payment')->__($method['title']) . " ($code)";
                    }

                    // for simple display
                    $methods[$code] = $title;

                    // for grouped display
                    $item = array('value' => $code, 'label' => $title);

                    if (isset($method['group'])) {
                        if (isset($groupedMethods[$method['group']])) {
                            $groupedMethods[$method['group']]['value'][$code] = $item;
                        } else {
                            $groupedMethods[$method['group']] = array(
                                    'label' => $method['group'],
                                    'value' => array($code => $item)
                            );
                        }
                    } else {
                        $groupedMethods[$code] = $item;
                    }
                }

                $block->addColumnAfter('payment_method', array(
                        'header' => $this->_getHelper()->__('Payment Method'),
                        'index' => 'payment_method',
                        'type' => 'options',
                        'width' => '50px',
                        'options' => $methods,
                        'option_groups' => $groupedMethods,
                        'filter_index' => version_compare(Mage::getVersion(), '1.4.1.1', '<') ? '_table_payment_method.value' : 'payment.method'
                ), 'status');

                $block->sortColumnsByOrder();
                $this->_updateGridCollection($block);
            }

            // case of multi virtual methods
            $column = $block->getColumn('payment_method');
            $groupedMethods = $column->getData('option_groups');
            $methods = $column->getData('options');

            foreach ($availableMethods as $code => $method) {
                if (preg_match('#^systempay_multi_[1-9]\d*x$#', $code)) {
                    unset($groupedMethods[$code]);

                    $title = $availableMethods['systempay_multi']['title'] . " ($code)";
                    $groupedMethods['systempay']['value'][$code] = array('value' => $code, 'label' => $title);
                    $methods[$code] = $title;
                }
            }
            $column->setData('option_groups', $groupedMethods);
            $column->setData('options', $methods);
        }
    }

    protected function _updateGridCollection($block)
    {
        $collection = $block->getCollection();

        if (version_compare(Mage::getVersion(), '1.4.1.1', '<')) {
            $paymentCollection = Mage::getResourceModel('sales/order_payment_collection');
            $entityTypeId = $paymentCollection->getEntity()->getTypeId();
            $methodAttrId = $paymentCollection->getEntity()->getAttribute('method')->getAttributeId();

            $collection->getSelect()
                ->joinLeft(array('_table_payment' => $collection->getTable('sales_order_entity')),
                    '`_table_payment`.`parent_id` = `e`.`entity_id` AND `_table_payment`.`entity_type_id` = ' . $entityTypeId,
                    array())

                ->joinLeft(array('_table_payment_method' => $collection->getTable('sales_order_entity_varchar')),
                    '(`_table_payment_method`.`entity_id` = `_table_payment`.`entity_id` AND `_table_payment_method`.`attribute_id` = ' . $methodAttrId . ')',
                    array('payment_method' => 'value'));
        } else {
            $paymentTable = $collection->getTable('sales/order_payment');

            $collection->getSelect()->joinLeft(
                    array('payment' => $paymentTable),
                    '(payment.parent_id = main_table.entity_id AND payment.entity_id = (SELECT min(p.entity_id) FROM ' . $paymentTable . ' p WHERE p.parent_id = main_table.entity_id))',
                    array('payment_method' => 'method')
            );
        }

        // clear collection
        $collection->clear();

        $this->_addPaymentMethodFilter($block);
        $this->_addPaymentMethodOrder($block);

        // reload collection
        $collection->load();
    }

    protected function _addPaymentMethodFilter($block)
    {
        $data = $block->getParam($block->getVarNameFilter(), null); // load filter params from request

        if (is_string($data)) {
            $data = Mage::helper('adminhtml')->prepareFilterString($data);
        }

        $column = $block->getColumn('payment_method');

        if (is_array($data) && isset($data['payment_method']) && strlen($data['payment_method']) > 0 && $column->getFilter()) {
            $column->getFilter()->setValue($data['payment_method']);

            $field = ($column->getFilterIndex()) ? $column->getFilterIndex() : $column->getIndex();
            $cond = $column->getFilter()->getCondition();
            if ($field && isset($cond)) {
                try {
                    $block->getCollection()->addFieldToFilter($field, $cond);
                } catch (Exception $e) {
                    $sql = $block->getCollection()->getConnection()->quoteInto("$field = ?", $cond['eq']);
                    $block->getCollection()->getSelect()->where($sql);
                }
            }
        }
    }

    protected function _addPaymentMethodOrder($block)
    {
        $columnId = $block->getParam($block->getVarNameSort(), null); // load sort column from request

        if ($columnId == 'payment_method') { // only override if sort column is ours
            $dir = $block->getParam($block->getVarNameDir(), null); // load sort dir from request
            $dir = (strtoupper($dir) == 'DESC') ? 'DESC' : 'ASC';

            $column = $block->getColumn('payment_method');

            $column->setDir($dir);
            $field = $column->getFilterIndex() ? $column->getFilterIndex() : $column->getIndex();

            $block->getCollection()->getSelect()->order($field . ' ' . $column->getDir());
        }
    }

    public function doOneclickQuoteProcess($observer)
    {
        $block = $observer->getBlock();

        if ($block->getNameInLayout() == 'cart_sidebar.extra_actions' || $block->getNameInLayout() == 'checkout.cart.methods') {
            $currentQuote = Mage::getSingleton('checkout/session')->getQuote();

            if ($currentQuote && $currentQuote->getItemsCount()) {
                $this->_oneclickQuoteProcess($currentQuote);
            }
        } elseif ($block->getNameInLayout() == 'alert.urls') {
            if ($block->getProduct() && $block->getProduct()->getId()) {
                $this->_oneclickQuoteProcess($block->getProduct());
            }
        }
    }

    protected function _oneclickQuoteProcess($data)
    {
        if (!Mage::getModel('systempay/payment_standard')->isOneclickAvailable()) {
            // no 1-Click payment
            return;
        }

        $session = Mage::getSingleton('systempay/session');
        $quote = $session->getQuote();

        // remove all 1-Click quote items to refresh it
        foreach ($quote->getItemsCollection() as $item) {
            $quote->removeItem($item->getId());
        }
        $quote->getShippingAddress()->removeAllShippingRates();
        $quote->setCouponCode('');

        // fill with current viewed element
        if ($data instanceof Mage_Catalog_Model_Product) {
            try {
                $result = $quote->addProduct($data);

                if (is_string($result)) {
                    $this->_getHelper()->log('Product view : ' . $result);
                }
            } catch (Exception $e) {
                $this->_getHelper()->log('Product view : ' . $e->getMessage());
            }
        } elseif ($data instanceof Mage_Sales_Model_Quote) {
            foreach ($data->getItemsCollection() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }

                $product = $item->getProduct();

                // retrieve all item options
                $option = $item->getOptionByCode('info_buyRequest');
                $request = new Varien_Object(
                    $option ? unserialize($option->getValue()) : array('product_id' => $product->getId())
                );
                $request->setQty($item->getQty());

                try {
                    $quote->addProduct($product, $request);
                } catch (Exception $e) {
                    $this->_getHelper()->log('Cart view : ' . $e->getMessage());
                }
            }

            // set coupon code if any
            $quote->setCouponCode($data->getCouponCode());
        }

        $quote->getShippingAddress()->setCollectShippingRates(true);
        $quote->setTotalsCollectedFlag(false);
        $quote->collectTotals()->save();

        $session->unsetQuote();
        $session->setQuoteId($quote->getId());
    }

    public function doOneclickUnsetQuote($observer)
    {
        $session = Mage::getSingleton('systempay/session');

        $session->getQuote()->delete();
        $session->unsetAll();
    }

    public function doPaymentButtonsManage($observer)
    {
        $block = $observer->getBlock();

        if (isset($block) && $block->getModuleName() == 'Mage_Adminhtml' && $block->getId() == 'sales_order_view') {
            $order = $block->getOrder();

            if ($order && $order->getPayment() && stripos($order->getPayment()->getMethod(), 'systempay_') === 0) {
                switch ($order->getStatus()) {
                    case 'systempay_to_validate':
                        $message = $this->_getHelper()->__('Are you sure you want to validate this order in Systempay platform ?');

                        $block->addButton('systempay_validate_payment', array(
                                'label'     => $this->_getHelper()->__('Validate payment'),
                                'onclick'   => "confirmSetLocation('{$message}', '{$block->getUrl('adminhtml/systempay_payment/validate')}')",
                                'class'     => 'go'
                        ));

                        // break omitted intentionally

                    case 'payment_review':
                        $block->removeButton('accept_payment');
                        break;

                    default:
                        break;
                }
            }
        }
    }

    public function doAfterPaymentSectionEdit($observer)
    {
        if (Mage::app()->getRequest()->getParam('section') !== 'payment') {
            return;
        }

        // response content
        $output = Mage::app()->getLayout()->getOutput();

        $preferedMaxInputVars = 0;
        $preferedMaxInputVars += substr_count($output, 'name="groups[');
        $preferedMaxInputVars += substr_count($output, 'name="config_state[');
        $preferedMaxInputVars += 100; // to take account of dynamically created inputs

        $block = Mage::app()->getLayout()->getMessagesBlock();
        if ((ini_get('suhosin.post.max_vars') && ini_get('suhosin.post.max_vars') < $preferedMaxInputVars)
                || (ini_get('suhosin.request.max_vars') && ini_get('suhosin.request.max_vars') < $preferedMaxInputVars)) {
            $block->addWarning($this->_getHelper()->__('Warning, please increase the suhosin patch for PHP post and request limits to save module configurations correctly. Recommended value is %s.', $preferedMaxInputVars));
        } elseif (ini_get('max_input_vars') && ini_get('max_input_vars') < $preferedMaxInputVars) {
            $block->addWarning($this->_getHelper()->__('Warning, please increase the value of the max_input_vars directive in php.ini to save module configurations correctly. Recommended value is %s.', $preferedMaxInputVars));
        }
    }

    /**
     * Return systempay data helper.
     *
     * @return Lyra_Systempay_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('systempay');
    }
}
