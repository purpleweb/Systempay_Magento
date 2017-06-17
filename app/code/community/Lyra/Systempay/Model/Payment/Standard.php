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

class Lyra_Systempay_Model_Payment_Standard extends Lyra_Systempay_Model_Payment_Abstract
{
    protected $_code = 'systempay_standard';
    protected $_formBlockType = 'systempay/standard';

    protected $_canSaveCc = true;

    protected function _setExtraFields($order)
    {
        $info = $this->getInfoInstance();

        if (!$this->_getHelper()->isAdmin() && ($this->isLocalCcType() || $this->isLocalCcInfo())) {
            // set payment_cards
            $this->_systempayRequest->set('payment_cards', $info->getCcType());
        } else {
            // payment_cards is given as csv by magento
            $paymentCards = explode(',', $this->getConfigData('payment_cards'));
            $paymentCards = in_array('', $paymentCards) ? '' : implode(';', $paymentCards);

            if ($paymentCards && $this->getConfigData('use_oney_in_standard')) {
                $testMode = $this->_systempayRequest->get('ctx_mode') == 'TEST';

                // add FacilyPay Oney payment cards
                $paymentCards .= ';' . ($testMode ? 'ONEY_SANDBOX' : 'ONEY');
            }
            $this->_systempayRequest->set('payment_cards', $paymentCards);
        }

        if ($this->_getHelper()->isAdmin()) {
            // set payment_src to MOTO for backend payments
            $this->_systempayRequest->set('payment_src', 'MOTO');
            return;
        }

        $session = Mage::getSingleton('systempay/session');
        if ($this->isIframeMode() && !$session->getSystempayOneclickPayment() /* no iframe in 1-Click */) {
            // iframe enabled and this is not 1-Click
            $themeConfig = $this->_getHelper()->getCommonConfigData('theme_config');
            if (!empty($themeConfig) && substr($themeConfig, -1) !== ';') {
                $themeConfig .= ';';
            }

            $themeConfig .= 'MODE_IFRAME=true;';
            $this->_systempayRequest->set('theme_config', $themeConfig);

            // enable automatic redirection
            $this->_systempayRequest->set('redirect_enabled', '1');
            $this->_systempayRequest->set('redirect_success_timeout', '0');
            $this->_systempayRequest->set('redirect_error_timeout', '0');

            $returnUrl = $this->_systempayRequest->get('url_return');
            $this->_systempayRequest->set('url_return', $returnUrl . '?iframe=true');
        }

        if (!$this->getConfigData('one_click_active') || !$order->getCustomerId()) {
            $this->_setCcInfo();
        } else {
            // 1-Click enabled and customer logged-in
            $customer = Mage::getModel('customer/customer');
            $customer->load($order->getCustomerId());

            if ($this->isIdentifierPayment($customer)) {
                // customer has an identifier and wants to use it
                $this->_getHelper()->log('Customer ' . $customer->getEmail() . ' has an identifier and chose to use it for payment.' . $customer->getSystempayIdentifier());
                $this->_systempayRequest->set('identifier', $customer->getSystempayIdentifier());
            } else {
                if ($this->isLocalCcInfo() && $info->getAdditionalData()) { // additional_data is used to stock cc_register flag
                    // customer wants to register card data

                    if ($customer->getSystempayIdentifier()) {
                        // customer has already an identifier
                        $this->_getHelper()->log('Customer ' . $customer->getEmail() . ' has an identifier and chose to update it with new card info.');
                        $this->_systempayRequest->set('identifier', $customer->getSystempayIdentifier());
                        $this->_systempayRequest->set('page_action', 'REGISTER_UPDATE_PAY');
                    } else {
                        $this->_getHelper()->log('Customer ' . $customer->getEmail() . ' has not identifier and chose to register his card info.');
                        $this->_systempayRequest->set('page_action', 'REGISTER_PAY');
                    }
                } elseif (!$this->isLocalCcInfo()) {
                    // card data entry on payment page, let's ask customer for data registration
                    $this->_getHelper()->log('Customer ' . $customer->getEmail() . ' will be asked for card data registration on payment page.');
                    $this->_systempayRequest->set('page_action', 'ASK_REGISTER_PAY');
                }

                $this->_setCcInfo();
            }
        }
    }

    protected function _setCcInfo()
    {
        if (!$this->isLocalCcInfo()) {
            return;
        }

        $info = $this->getInfoInstance();

        $cardData = explode(' - ', $info->getCcNumber());

        $this->_systempayRequest->set('cvv', $cardData[0]);
        $this->_systempayRequest->set('card_number', $cardData[1]);
        $this->_systempayRequest->set('expiry_year', $info->getCcExpYear());
        $this->_systempayRequest->set('expiry_month', $info->getCcExpMonth());

        // override action_mode
        $this->_systempayRequest->set('action_mode', 'SILENT');
    }

    protected function _proposeOney()
    {
        $info = $this->getInfoInstance();

        return (!$info->getCcType() && $this->getConfigData('use_oney_in_standard'))
            || in_array($info->getCcType(), array('ONEY_SANDBOX', 'ONEY'));
    }

    /**
     * Return available card types
     *
     * @return string
     */
    public function getAvailableCcTypes()
    {
        // all cards
        $allCards = Lyra_Systempay_Model_Api_Api::getSupportedCardTypes();

        // selected cards from module configuration
        $cards = $this->getConfigData('payment_cards');

        if (!empty($cards)) {
            $cards = explode(',', $cards);
        } else {
            $cards = array_keys($allCards);
            $cards = array_diff($cards, array('ONEY_SANDBOX', 'ONEY'));
        }

        if (!$this->_getHelper()->isAdmin() && $this->isLocalCcType() && $this->getConfigData('use_oney_in_standard')) {
            $testMode = $this->_getHelper()->getCommonConfigData('ctx_mode') == 'TEST';

            $cards[] = $testMode ? 'ONEY_SANDBOX' : 'ONEY';
        }

        $availCards = array();
        foreach ($allCards as $code => $label) {
            if (in_array($code, $cards)) {
                $availCards[$code] = $label;
            }
        }
        return $availCards;
    }

    public function isOneclickAvailable()
    {
        if (!$this->isAvailable()) {
            return false;
        }

        // no 1-Click
        if (!$this->getConfigData('one_click_active')) {
            return false;
        }

        if ($this->_getHelper()->isAdmin()) {
            return false;
        }

        $session = Mage::getSingleton('customer/session');

        // customer not logged in
        if (!$session->isLoggedIn()) {
            return false;
        }

        // customer has not Systempay identifier
        $customer = $session->getCustomer();
        if (!$customer || !$customer->getSystempayIdentifier()) {
            return false;
        }

        return true;
    }

    public function isIdentifierPayment($customer)
    {
        $info = $this->getInfoInstance();

        // payment by identifier
        return $customer->getSystempayIdentifier() && $info->getAdditionalInformation(Lyra_Systempay_Helper_Payment::IDENTIFIER);
    }

    /**
     * Assign data to info model instance
     *
     * @param   mixed $data
     * @return  Mage_Payment_Model_Info
     */
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $info = $this->getInfoInstance();

        if ($data->getSystempayStandardUseIdentifier()) {
            $info->setCcType(null)
                    ->setCcLast4(null)
                    ->setCcNumber(null)
                    ->setCcCid(null)
                    ->setCcExpMonth(null)
                    ->setCcExpYear(null)
                    ->setAdditionalData(null)
                    ->setAdditionalInformation(Lyra_Systempay_Helper_Payment::IDENTIFIER, true); // payment by identifier
        } else {
            // set card info
            $info->setCcType($data->getSystempayStandardCcType())
                    ->setCcLast4(substr($data->getSystempayStandardCcNumber(), -4))
                    ->setCcNumber($data->getSystempayStandardCcNumber())
                    ->setCcCid($data->getSystempayStandardCcCvv())
                    ->setCcExpMonth($data->getSystempayStandardCcExpMonth())
                    ->setCcExpYear($data->getSystempayStandardCcExpYear())
                    ->setAdditionalData($data->getSystempayStandardCcRegister()) // wether to register data
                    ->setAdditionalInformation(Lyra_Systempay_Helper_Payment::IDENTIFIER, false);
        }

        return $this;
    }

    /**
     * Prepare info instance for save
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function prepareSave()
    {
        $info = $this->getInfoInstance();
        if ($this->_canSaveCc) {
            if ($info->getCcNumber()) {
                $info->setCcNumberEnc($info->encrypt($info->getCcCid() . ' - ' . $info->getCcNumber()));
            } else {
                $info->setCcNumberEnc(null);
            }
        }

        $info->setCcNumber(null);
        $info->setCcCid(null);
        return $this;
    }

    /**
     * Method that will be executed instead of authorize or capture
     * if flag isInitializeNeeded set to true
     *
     * @param string $paymentAction
     * @param object $stateObject
     *
     * @return Mage_Payment_Model_Abstract
     */
    public function initialize($paymentAction, $stateObject)
    {
        parent::initialize($paymentAction, $stateObject);

        if ($this->_getHelper()->isAdmin() && $this->_getHelper()->isCurrentlySecure()) {
            // do instant payment by WS
            $stateObjectResult = $this->_doInstantPayment($this->getInfoInstance());

            $stateObject->setState($stateObjectResult->getState());
            $stateObject->setStatus($stateObjectResult->getStatus());
            $stateObject->setIsNotified($stateObjectResult->getIsNotified());
        }

        return $this;
    }

    /**
     * The URL the customer is redirected to after clicking on "Confirm order".
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        if ($this->isIframeMode()) {
            return Mage::getUrl('systempay/payment/iframe', array('_secure' => true));
        }

        return parent::getOrderPlaceRedirectUrl();
    }

    /**
     * Call gateway by WS to do an instant payment
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @return Varien_Object
     */
    protected function _doInstantPayment($payment)
    {
        $order = $payment->getOrder();
        $storeId = $order->getStore()->getId();

        $this->_getHelper()->log("Instant payment using WS for order #{$order->getId()}.");

        $requestId = '';

        try {
            $wsApi = $this->checkAndGetWsApi($storeId);

            $timestamp = time();

            // common request generation
            $commonRequest = new \Lyra\Systempay\Model\Api\Ws\CommonRequest();
            $commonRequest->setPaymentSource('MOTO');
            $commonRequest->setSubmissionDate(new DateTime("@$timestamp"));

            // amount in current order currency
            $amount = $order->getGrandTotal();

            // retrieve currency
            $currency = Lyra_Systempay_Model_Api_Api::findCurrencyByAlphaCode($order->getOrderCurrencyCode());
            if ($currency == null) {
                // if currency is not supported, use base currency
                $currency = Lyra_Systempay_Model_Api_Api::findCurrencyByAlphaCode($order->getBaseCurrencyCode());

                // ... and order total in base currency
                $amount = $order->getBaseGrandTotal();
            }

            // payment request generation
            $paymentRequest = new \Lyra\Systempay\Model\Api\Ws\PaymentRequest();
            $paymentRequest->setTransactionId(Lyra_Systempay_Model_Api_Api::generateTransId($timestamp));
            $paymentRequest->setAmount($currency->convertAmountToInteger($amount));
            $paymentRequest->setCurrency($currency->getNum());

            $captureDelay = $this->getConfigData('capture_delay', $storeId); // get sub-module specific param
            if (!is_numeric($captureDelay)) {
                $captureDelay = $this->_getHelper()->getCommonConfigData('capture_delay', $storeId); // get general param
            }
            if (is_numeric($captureDelay)) {
                $paymentRequest->setExpectedCaptureDate(new DateTime('@' . strtotime("+$captureDelay days", $timestamp)));
            }

            $validationMode = $this->getConfigData('validation_mode', $storeId); // get sub-module specific param
            if ($validationMode === '-1') {
                $validationMode = $this->_getHelper()->getCommonConfigData('validation_mode', $storeId); // get general param
            }
            if ($validationMode !== '') {
                $paymentRequest->setManualValidation($validationMode);
            }

            // order request generation
            $orderRequest = new \Lyra\Systempay\Model\Api\Ws\OrderRequest();
            $orderRequest->setOrderId($order->getIncrementId());

            // card request generation
            $cardRequest = new \Lyra\Systempay\Model\Api\Ws\CardRequest();
            $info = $this->getInfoInstance();
            $cardRequest->setNumber($info->getCcNumber());
            $cardRequest->setScheme($info->getCcType());
            $cardRequest->setCardSecurityCode($info->getCcCid());
            $cardRequest->setExpiryMonth($info->getCcExpMonth());
            $cardRequest->setExpiryYear($info->getCcExpYear());

            // billing details generation
            $billingDetailsRequest = new \Lyra\Systempay\Model\Api\Ws\BillingDetailsRequest();
            $billingDetailsRequest->setReference($order->getCustomerId());

            if ($order->getBillingAddress()->getPrefix()) {
                $billingDetailsRequest->setTitlte($order->getBillingAddress()->getPrefix());
            }

            $billingDetailsRequest->setFirstName($order->getBillingAddress()->getFirstname());
            $billingDetailsRequest->setLastName($order->getBillingAddress()->getLastname());
            $billingDetailsRequest->setPhoneNumber($order->getBillingAddress()->getTelephone());
            $billingDetailsRequest->setEmail($order->getCustomerEmail());
            $billingDetailsRequest->setAddress(trim($order->getBillingAddress()->getStreet(1) . ' ' . $order->getBillingAddress()->getStreet(2)));
            $billingDetailsRequest->setZipCode($order->getBillingAddress()->getPostcode());
            $billingDetailsRequest->setCity($order->getBillingAddress()->getCity());
            $billingDetailsRequest->setState($order->getBillingAddress()->getRegion());
            $billingDetailsRequest->setCountry($order->getBillingAddress()->getCountryId());

            // language
            $currentLang = substr(Mage::app()->getLocale()->getLocaleCode(), 0, 2);
            if (Lyra_Systempay_Model_Api_Api::isSupportedLanguage($currentLang)) {
                $language = $currentLang;
            } else {
                $language = $this->_getHelper()->getCommonConfigData('language', $storeId);
            }
            $billingDetailsRequest->setLanguage($language);

            // shipping details generation
            $shippingDetailsRequest = new \Lyra\Systempay\Model\Api\Ws\ShippingDetailsRequest();

            $address = $order->getShippingAddress();
            if (is_object($address)) { // deliverable order
                $shippingDetailsRequest->setFirstName($address->getFirstname());
                $shippingDetailsRequest->setLastName($address->getLastname());
                $shippingDetailsRequest->setPhoneNumber($address->getTelephone());
                $shippingDetailsRequest->setAddress($address->getStreet(1));
                $shippingDetailsRequest->setAddress2($address->getStreet(2));
                $shippingDetailsRequest->setZipCode($address->getPostcode());
                $shippingDetailsRequest->setCity($address->getCity());
                $shippingDetailsRequest->setState($address->getRegion());
                $shippingDetailsRequest->setCountry($address->getCountryId());
            }

            // extra details generation
            $extraDetailsRequest = new \Lyra\Systempay\Model\Api\Ws\ExtraDetailsRequest();
            $extraDetailsRequest->setIpAddress($this->_getHelper()->getIpAddress());

            // customer request generation
            $customerRequest = new \Lyra\Systempay\Model\Api\Ws\CustomerRequest();
            $customerRequest->setBillingDetails($billingDetailsRequest);
            $customerRequest->setShippingDetails($shippingDetailsRequest);
            $customerRequest->setExtraDetails($extraDetailsRequest);

            // create payment object generation
            $createPayment = new \Lyra\Systempay\Model\Api\Ws\CreatePayment();
            $createPayment->setCommonRequest($commonRequest);
            $createPayment->setPaymentRequest($paymentRequest);
            $createPayment->setOrderRequest($orderRequest);
            $createPayment->setCardRequest($cardRequest);
            $createPayment->setCustomerRequest($customerRequest);

            // do createPayment WS call
            $requestId = $wsApi->setHeaders();
            $createPaymentResponse = $wsApi->createPayment($createPayment);

            $wsApi->checkAuthenticity();
            $wsApi->checkResult($createPaymentResponse->getCreatePaymentResult()->getCommonResponse(),
                    array('INITIAL', 'NOT_CREATED', 'AUTHORISED', 'AUTHORISED_TO_VALIDATE',
                            'WAITING_AUTHORISATION', 'WAITING_AUTHORISATION_TO_VALIDATE')
            );

            // check operation type (0: debit, 1 refund)
            $transType = $createPaymentResponse->getCreatePaymentResult()->getPaymentResponse()->getOperationType();
            if ($transType != 0) {
                throw new Exception("Unexpected transaction type returned ($transType).");
            }

            // update authorized amount
            $payment->setAmountAuthorized($order->getTotalDue());
            $payment->setBaseAmountAuthorized($order->getBaseTotalDue());

            $wrapper = new Lyra_Systempay_Model_Api_Ws_ResultWrapper(
                    $createPaymentResponse->getCreatePaymentResult()->getCommonResponse(),
                    $createPaymentResponse->getCreatePaymentResult()->getPaymentResponse(),
                    $createPaymentResponse->getCreatePaymentResult()->getAuthorizationResponse(),
                    $createPaymentResponse->getCreatePaymentResult()->getCardResponse(),
                    $createPaymentResponse->getCreatePaymentResult()->getThreeDSResponse(),
                    $createPaymentResponse->getCreatePaymentResult()->getFraudManagementResponse()
            );

            // retrieve new order state and status
            $stateObject = $this->_getPaymentHelper()->nextOrderState($wrapper, $order);
            $this->_getHelper()->log("Order #{$order->getId()}, new state : {$stateObject->getState()}, new status : {$stateObject->getStatus()}.");

            $order->setState($stateObject->getState(), $stateObject->getStatus(), $wrapper->getMessage());
            if ($stateObject->getState() == Mage_Sales_Model_Order::STATE_HOLDED) { // for Magento 1.4.0.x
                $stateObject->setState($stateObject->getBeforeState());
                $stateObject->setStatus($stateObject->getBeforeStatus());
            }

            // save platform responses
            $this->_getPaymentHelper()->updatePaymentInfo($order, $wrapper);

            // try to create invoice
            $this->_getPaymentHelper()->createInvoice($order);

            $stateObject->setIsNotified(true);
            return $stateObject;

        } catch(Lyra_Systempay_Model_WsException $e) {
            $this->_getHelper()->log("[$requestId] {$e->getMessage()}", Zend_Log::WARN);

            $warn = $this->_getHelper()->__('Please correct this error to use Systempay web services.');
            $this->_getAdminSession()->addWarning($warn);
            $this->_getAdminSession()->addError($this->_getHelper()->__($e->getMessage()));
            Mage::throwException('');
        } catch(SoapFault $f) {
            $this->_getHelper()->log("[$requestId] SoapFault with code {$f->faultcode}: {$f->faultstring}.", Zend_Log::WARN);

            $warn = $this->_getHelper()->__('Please correct this error to use Systempay web services.');
            $this->_getAdminSession()->addWarning($warn);
            $this->_getAdminSession()->addError($f->faultstring);
            Mage::throwException('');
        } catch(\Lyra\Systempay\Model\Api\Ws\SecurityException $e) {
            $this->_getHelper()->log("[$requestId] " . $e->getMessage(), Zend_Log::ERR);

            $this->_getAdminSession()->addError($this->_getHelper()->__('Authentication error !'));
            Mage::throwException('');
        } catch(\Lyra\Systempay\Model\Api\Ws\ResultException $e) {
            $this->_getHelper()->log("[$requestId] createPayment error with code {$e->getRealCode()}: {$e->getMessage()}.", Zend_Log::WARN);

            $this->_getAdminSession()->addError($e->getMessage());
            Mage::throwException('');
        } catch (Exception $e) {
            $this->_getHelper()->log("[$requestId] Exception with code {$e->getCode()}: {$e->getMessage()}", Zend_Log::ERR);

            $this->_getAdminSession()->addError($e->getMessage());
            Mage::throwException('');
        }
    }

    /**
     * Check if the card data entry on merchant site option is selected
     * @return boolean
     */
    public function isLocalCcInfo()
    {
        return $this->_getHelper()->isCurrentlySecure() // this is a double check, it's also done on backend side
                    && $this->getConfigData('card_info_mode') == 3;
    }

    /**
     * Return true if iframe mode is enabled.
     *
     * @return string
     */
    public function isIframeMode()
    {
        return $this->getConfigData('card_info_mode') == 4;
    }

    /**
     * Check if the local card type selection option is choosen
     * @return boolean
     */
    public function isLocalCcType()
    {
        return $this->getConfigData('card_info_mode') == 2;
    }
}
