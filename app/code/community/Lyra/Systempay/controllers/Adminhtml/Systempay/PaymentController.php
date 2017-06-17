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

class Lyra_Systempay_Adminhtml_Systempay_PaymentController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Redirect customer to the payment gateway.
     */
    public function formAction()
    {
        $this->_getDataHelper()->log('Start =================================================');
        $this->_getPaymentHelper()->doPaymentForm($this);
        $this->_getDataHelper()->log('End =================================================');
    }

    /**
     * Action called after the client returns from payment gateway.
     */
    public function returnAction()
    {
        $this->_getDataHelper()->log('Start =================================================');
        $this->_getPaymentHelper()->doPaymentReturn($this);
        $this->_getDataHelper()->log('End =================================================');
    }

    /**
     * Action called when Validate payment button is clicked in backend order view.
     */
    public function validateAction()
    {
        $this->_getDataHelper()->log('Start =================================================');

        $adminSession = Mage::getSingleton('adminhtml/session');
        $adminSession->getMessages(true);

        // retrieve order to validate
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);
        if (!$order->getId()) {
            $adminSession->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/*/');
            return;
        }

        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);

        $payment = $order->getPayment();
        $payment->getMethodInstance()->validatePayment($payment);
        $this->_redirect('adminhtml/sales_order/view', array('order_id' => $order->getId()));

        $this->_getDataHelper()->log('End =================================================');
    }

    /**
     * Redirect to checkout initial page (when payment cannot be done).
     *
     * @param string $msg
     */
    public function redirectBack($msg)
    {
        // clear all messages from session
        $this->getCheckout()->getMessages(true);
        Mage::getSingleton('adminhtml/session')->getMessages(true);

        Mage::getSingleton('adminhtml/session')->addError($this->__($msg));

        $this->_getDataHelper()->log($msg . ' Redirecting to create order page.');
        $this->_redirect('adminhtml/sales_order_create/index');
    }

    /**
     * Redirect to error page (when an unexpected error occured).
     *
     * @param Mage_Sales_Model_Order $order
     */
    public function redirectError($order)
    {
        // clear all messages from session
        $this->getCheckout()->getMessages(true);
        Mage::getSingleton('adminhtml/session')->getMessages(true);

        Mage::getSingleton('adminhtml/session')->addError(
            $this->__('An error has occured during the payment process.')
        );
        $this->_redirect('adminhtml/sales_order_create/index');
    }

    /**
     * Redirect to result page (according to payment status).
     *
     * @param Mage_Sales_Model_Order $order
     * @param boolean $success
     * @param boolean $checkUrlWarn
     */
    public function redirectResponse($order, $success, $checkUrlWarn = false)
    {
        $adminSession = Mage::getSingleton('adminhtml/session');

        // clear all messages in session
        $this->getCheckout()->getMessages(true);
        $adminSession->getMessages(true);

        $storeId = $order->getStore()->getId();
        if ($this->_getDataHelper()->getCommonConfigData('ctx_mode', $storeId) == 'TEST') {
            // display going to production message
            $message = $this->__('<p><u>GOING INTO PRODUCTION</u></p>You want to know how to put your shop into production mode, please go to this URL : ');
            $message .= '<a href="https://paiement.systempay.fr/html/faq/prod" target="_blank">https://paiement.systempay.fr/html/faq/prod</a>';
            $adminSession->addNotice($message);

            if ($checkUrlWarn) {
                // order not validated by notification URL, in TEST mode, user is webmaster
                // so display a warning about notification URL not working

                if ($this->_getDataHelper()->isMaintenanceMode()) {
                    $message = $this->__('The shop is in maintenance mode.The automatic notification cannot work.');
                } else {
                    $message = $this->__('The automatic validation hasn\'t worked. Have you correctly set up the notification URL in your Systempay Back Office ?');
                    $message .= '<br /><br />';
                    $message .= $this->__('For understanding the problem, please read the documentation of the module :<br />&nbsp;&nbsp;&nbsp;- Chapter &laquo;To read carefully before going further&raquo;<br />&nbsp;&nbsp;&nbsp;- Chapter &laquo;Notification URL settings&raquo;');
                }

                $adminSession->addError($message);
            }
        }

        if ($success) {
            $this->_getDataHelper()->log('Redirecting to order review page.');
            $this->getCheckout()->setLastSuccessQuoteId($order->getQuoteId());
            $adminSession->addSuccess($this->__('The payment was successful. Your order was registered successfully.'));
            $this->_redirect('adminhtml/sales_order/view', array('order_id' => $order->getId()));
        } else {
            $this->_getDataHelper()->log('Unsetting order data in session.');
            $this->getCheckout()->unsLastQuoteId()
                                ->unsLastSuccessQuoteId()
                                ->unsLastOrderId()
                                ->unsLastRealOrderId();

            $this->_getDataHelper()->log('Redirecting to order review page.');
            $adminSession->addWarning($this->__('Checkout and order have been canceled.'));
            $this->_redirect('adminhtml/sales_order/view', array('order_id' => $order->getId()));
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/create');
    }

    /**
     * Get checkout session namespace.
     *
     * @return Mage_Adminhtml_Model_Session_Quote
     */
    public function getCheckout()
    {
        return Mage::getSingleton('adminhtml/session_quote');
    }

    /**
     * Return Systempay data helper.
     *
     * @return Lyra_Systempay_Helper_Data
     */
    protected function _getDataHelper()
    {
        return Mage::helper('systempay');
    }

    /**
     * Return Systempay payment helper.
     *
     * @return Mage_Systempay_Helper_Payment
     */
    protected function _getPaymentHelper()
    {
        return Mage::helper('systempay/payment');
    }
}
