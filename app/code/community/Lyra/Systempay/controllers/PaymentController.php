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

class Lyra_Systempay_PaymentController extends Mage_Core_Controller_Front_Action
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
     * Redirect customer to the payment gateway iframe.
     */
    public function iframeAction()
    {
        $this->_getDataHelper()->log('Start =================================================');
        $this->_getPaymentHelper()->doPaymentForm($this);
        $this->_getDataHelper()->log('End =================================================');
    }

    /**
     * Display iframe loader.
     */
    public function loaderAction()
    {
        $block = $this->getLayout()->createBlock('core/template')->setTemplate('systempay/iframe/loader.phtml');
        $this->getResponse()->setBody($block->toHtml());
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
     * Action called by the payment gateway to notify payment result.
     * Note that admin payment also are notified by this action.
     */
    public function checkAction()
    {
        $this->_getDataHelper()->log('Start =================================================');
        $this->_getPaymentHelper()->doPaymentCheck($this);
        $this->_getDataHelper()->log('End =================================================');
    }

    /**
     * AJAX Action called when customer choose a new shipping address in 1-Click payment UI.
     */
    public function oneclickShippingAction()
    {
        if ($this->_ajaxExpire()) {
            return;
        }

        try {
            $oneClickQuote = $this->_updateOneclickQuote(true);
            $oneClickQuote->collectTotals()->save();
            $this->getSystempaySession()->unsetQuote();

            $layout = $this->getLayout();
            $update = $layout->getUpdate();
            $update->load('systempay_oneclick_shipping_method');
            $layout->generateXml();
            $layout->generateBlocks();

            $this->_returnJson(array('html' => $layout->getOutput()));
        } catch (Mage_Core_Exception $e) {
            $this->_returnJson(array('error' => true, 'message' => $e->getMessage()));
        }
    }

    /**
     * Action called when customer click Systempay 1-Click payment button.
     */
    public function oneclickPaymentAction()
    {
        $this->_getDataHelper()->log('Start =================================================');

        try {
            $this->_getDataHelper()->log(
                'Update Systempay 1-Click quote data (products, shipping address, shipping method).'
            );
            $oneClickQuote = $this->_updateOneclickQuote();

            // reload billing address
            $this->_getDataHelper()->log('Refresh Systempay 1-Click quote billing address.');
            $customerAddressId = $oneClickQuote->getBillingAddress()->getCustomerAddressId();
            $customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
            $oneClickQuote->getBillingAddress()->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);

            $this->_getDataHelper()->log('Add payment info to Systempay 1-Click quote.');
            if (!$oneClickQuote->isVirtual() && $oneClickQuote->getShippingAddress()) {
                $oneClickQuote->getShippingAddress()->setPaymentMethod('systempay_standard');
            } else {
                $oneClickQuote->getBillingAddress()->setPaymentMethod('systempay_standard');
            }

            $data = array('method' => 'systempay_standard', 'systempay_standard_use_identifier' => 1);
            $oneClickQuote->getPayment()->importData($data);

            $this->_getDataHelper()->log('Save Systempay 1-Click quote after total recollection.');
            $oneClickQuote->collectTotals()->setIsActive(true)->save();

            // reload Systempay 1-Click quote
            $this->getSystempaySession()->unsetQuote();
            $oneClickQuote = $this->getSystempaySession()->getQuote();

            if ($this->getCheckout()->getQuoteId()) {
                // save current quote ID to reload it farther
                $this->getSystempaySession()->setSystempayInitialQuoteId($this->getCheckout()->getQuoteId());
                $this->getCheckout()->getQuote()->setIsActive(false)->save();
            }

            // save Systempay 1-Click quote to checkout session
            $this->getSystempaySession()->setSystempayOneclickPayment(true)
                                     ->setSystempayOneclickBackUrl($this->_getRefererUrl());
            $this->getCheckout()->replaceQuote($oneClickQuote);

            $this->_getDataHelper()->log('Create order from Systempay 1-Click quote.');
            $service = Mage::getModel('sales/service_quote', $oneClickQuote);
            $order = $service->submit();

            $this->_getDataHelper()->log(
                'Set Systempay 1-Click payment information to session and redirect to payment page.'
            );
            $this->getCheckout()->setLastSuccessQuoteId($this->getCheckout()->getQuoteId())
                                ->setLastRealOrderId($order->getIncrementId());

            $redirectUrl = Mage::getUrl('systempay/payment/form', array('_secure' => true));
            $this->_redirectUrl($redirectUrl);
        } catch (Mage_Core_Exception $e) {
            $this->_getDataHelper()->log(
                'Error when trying to pay with Systempay 1-Click. ' . $e->getMessage(),
                Zend_Log::WARN
            );

            // disable Systempay 1-Click quote
            $oneClickQuote = $this->getSystempaySession()->getQuote();
            $oneClickQuote->setIsActive(false)->setReservedOrderId(null)->save();

            // restore initial checkout quote
            if ($this->getSystempaySession()->getSystempayInitialQuoteId()) {
                $quote = Mage::getModel('sales/quote')->load((int)$this->getSystempaySession()->getSystempayInitialQuoteId(true));

                if ($quote->getId()) {
                    $quote->setIsActive(true)->save();
                    $this->getCheckout()->replaceQuote($quote);
                }
            }

            $this->getSystempaySession()->unsSystempayOneclickPayment()
                                     ->unsSystempayOneclickBackUrl();

            // use core/session instance to be able to show messages from all pages
            if (Mage::getSingleton('core/session')->getUseNotice(true)) {
                Mage::getSingleton('core/session')->addNotice($e->getMessage());
            } else {
                Mage::getSingleton('core/session')->addError($e->getMessage());
            }

            $this->_getDataHelper()->log('Redirecting to referer URL.');
            $this->_redirectUrl($this->_getRefererUrl());
        }

        $this->_getDataHelper()->log('End =================================================');
    }

    protected function _ajaxExpire()
    {
        $session = $this->getSystempaySession();

        if (!$this->getRequest()->isPost() || !$session->getQuote() || $session->getQuote()->getHasError()) {
            $this->getResponse()
                    ->setHeader('HTTP/1.1', '403 Session Expired')
                    ->setHeader('Login-Required', 'true')
                    ->sendResponse();
            return true;
        }

        return false;
    }

    protected function _returnJson($result)
    {
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    protected function _updateOneclickQuote($ignoreNotices = false)
    {
        $oneClickQuote = $this->getSystempaySession()->getQuote();

        if ($productId = $this->getRequest()->getPost('product', false)) {
            $product = Mage::getModel('catalog/product')
                    ->setStoreId(Mage::app()->getStore()->getId())
                    ->load((int)$productId);

            if ($product->getId()) {
                // remove all 1-Click quote items to refresh it
                foreach ($oneClickQuote->getItemsCollection() as $item) {
                    $oneClickQuote->removeItem($item->getId());
                }
                $oneClickQuote->getShippingAddress()->removeAllShippingRates();
                $oneClickQuote->setCouponCode('');

                $request = new Varien_Object($this->getRequest()->getParams());
                if (!$request->hasQty()) {
                    $request->setQty(1);
                }

                try {
                    $result = $oneClickQuote->addProduct($product, $request);

                    if (is_string($result)) {
                        // error message
                        Mage::getSingleton('core/session')->setUseNotice(true);
                        Mage::throwException($result);
                    }
                } catch (Mage_Core_Exception $e) {
                    Mage::throwException($e->getMessage());
                } catch (Exception $e) {
                    Mage::throwException(
                        $this->__('Cannot pay requested product with &laquo;Systempay Buy now&raquo;.')
                    );
                }

                // related products
                $productIds = $this->getRequest()->getParam('related_product');
                if (!empty($productIds)) {
                    $productIds = explode(',', $productIds);

                    if (!empty($productIds)) {
                        $allAvailable = true;
                        $allAdded = true;

                        foreach ($productIds as $productId) {
                            $productId = (int) $productId;
                            if (!$productId) {
                                continue;
                            }

                            $product = Mage::getModel('catalog/product')
                                    ->setStoreId(Mage::app()->getStore()->getId())
                                    ->load($productId);
                            if ($product->getId() && $product->isVisibleInCatalog()) {
                                try {
                                    $oneClickQuote->addProduct($product);
                                } catch (Exception $e) {
                                    $allAdded = false;
                                }
                            } else {
                                $allAvailable = false;
                            }
                        }

                        if (!$ignoreNotices) {
                            if (!$allAvailable) {
                                Mage::getSingleton('core/session')->addError(
                                    $this->__('Some of the products you requested are unavailable.')
                                );
                            }

                            if (!$allAdded) {
                                Mage::getSingleton('core/session')->addError(
                                    $this->__('Some of the products you requested are not available in the desired quantity.')
                                );
                            }
                        }
                    }
                }
            }
        }

        $addressId = $this->getRequest()->getPost('shipping_address', false);
        $customerAddress = Mage::getModel('customer/address')->load((int)$addressId);
        if (!$oneClickQuote->isVirtual() && $customerAddress->getId()) {
            if ($customerAddress->getCustomerId() != Mage::getSingleton('customer/session')->getCustomer()->getId()) {
                Mage::throwException($this->__('Customer Address is not valid.'));
            }

            $oneClickQuote->getShippingAddress()->importCustomerAddress($customerAddress);

            $method = $this->getRequest()->getPost('shipping_method', false);
            $oneClickQuote->getShippingAddress()->setShippingMethod($method);
        }

        $oneClickQuote->getShippingAddress()->setCollectShippingRates(true);
        $oneClickQuote->setTotalsCollectedFlag(false);

        return $oneClickQuote;
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
        Mage::getSingleton('core/session')->getMessages(true);

        $this->_getDataHelper()->log($msg . ' Redirecting to cart page.');
        $this->_redirect('checkout/cart', array('_store' => Mage::app()->getStore()->getId()));
    }

    /**
     * Redirect to error page (when an unexpected error occured).
     *
     * @param Mage_Sales_Model_Order $order
     */
    public function redirectError($order)
    {
        // clear all messages in session
        $this->getCheckout()->getMessages(true);
        Mage::getSingleton('core/session')->getMessages(true);

        $this->_getDataHelper()->log('Redirecting to failure page.');
        $this->_redirect('checkout/onepage/failure', array('_store' => $order->getStore()->getId()));
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
        // clear all messages in session
        $this->getCheckout()->getMessages(true);
        Mage::getSingleton('core/session')->getMessages(true);

        $storeId = $order->getStore()->getId();
        if ($this->_getDataHelper()->getCommonConfigData('ctx_mode', $storeId) == 'TEST') {
            // display going to production message
            $message = $this->__('<p><u>GOING INTO PRODUCTION</u></p>You want to know how to put your shop into production mode, please go to this URL : ');
            $message .= '<a href="https://paiement.systempay.fr/html/faq/prod" target="_blank">https://paiement.systempay.fr/html/faq/prod</a>';
            Mage::getSingleton('core/session')->addNotice($message);

            if ($checkUrlWarn) {
                // order not validated by notification URL, in TEST mode, user is webmaster
                // so display a warning about notification URL not working

                if ($this->_getDataHelper()->isMaintenanceMode()) {
                    $message = $this->__('The shop is in maintenance mode.The automatic notification cannot work.');
                } else {
                    $message = $this->__('The automatic validation has not worked. Have you correctly set up the notification URL in your Systempay Back Office ?');
                    $message .= '<br /><br />';
                    $message .= $this->__('For understanding the problem, please read the documentation of the module :<br />&nbsp;&nbsp;&nbsp;- Chapter &laquo;To read carefully before going further&raquo;<br />&nbsp;&nbsp;&nbsp;- Chapter &laquo;Notification URL settings&raquo;');
                }

                Mage::getSingleton('core/session')->addError($message);
            }
        }

        // was this a Systempay 1-click payment ?
        $oneclick = $this->getSystempaySession()->getSystempayOneclickPayment(true);

        if ($success) {
            if ($oneclick) {
                $this->getSystempaySession()->unsetAll();
            }

            $this->getCheckout()->setLastQuoteId($order->getQuoteId())
                                ->setLastSuccessQuoteId($order->getQuoteId())
                                ->setLastOrderId($order->getId())
                                ->setLastRealOrderId($order->getIncrementId())
                                ->setLastOrderStatus($order->getStatus());

            $this->_getDataHelper()->log('Redirecting to success page.');
            $this->_redirect('checkout/onepage/success', array('_store' => $storeId));
        } else {
            $this->_getDataHelper()->log('Unsetting order data in session.');
            $this->getCheckout()->unsetAll();

            Mage::getSingleton('core/session')->addWarning($this->__('Checkout and order have been canceled.'));

            if ($oneclick) {
                $oneClickQuote = $this->getSystempaySession()->getQuote();
                $oneClickQuote->setReservedOrderId(null)->save();
                $this->getSystempaySession()->unsetQuote();

                $this->getSystempaySession()->unsSystempayInitialQuoteId();

                // in case of 1-Click payment , redirect to referer URL
                $this->_getDataHelper()->log('Redirecting to referer URL (product view or cart page).');
                $this->_redirectUrl($this->getSystempaySession()->getSystempayOneclickBackUrl(true));
            } else {
                $this->_getDataHelper()->log("Restore cart for order #{$order->getId()} to allow re-order quicker.");

                $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
                if ($quote->getId()) {
                    $quote->setIsActive(true)->setReservedOrderId(null)->save();
                    $this->getCheckout()->replaceQuote($quote);
                }

                $this->_getDataHelper()->log('Redirecting to cart page.');
                $this->_redirect('checkout/cart', array('_store' => $storeId));
            }
        }
    }

    /**
     * Set redirect into response
     *
     * @param string $path
     * @param array $arguments
     * @return Mage_Core_Controller_Varien_Action
     */
    protected function _redirect($path, $arguments = array())
    {
        if ($this->getRequest()->getParam('iframe', false) /* if iframe payment */) {
            $block = $this->getLayout()->createBlock('systempay/iframe_response')
                                        ->setForwardUrl(Mage::getUrl($path, $arguments));

            $this->getResponse()->setBody($block->toHtml());
            return $this;
        } else {
            return parent::_redirect($path, $arguments);
        }
    }

    /**
     * Get Systempay 1-Click checkout session namespace.
     *
     * @return Lyra_Systempay_Model_Session
     */
    public function getSystempaySession()
    {
        return Mage::getSingleton('systempay/session');
    }

    /**
     * Get checkout session namespace.
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
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
