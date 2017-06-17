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

namespace Lyra\Systempay\Model\Api\Ws;

class WsApi extends \SoapClient
{
    const HEADER_NAMESPACE = 'http://v5.ws.vads.lyra.com/Header/';
    const DATE_FORMAT = 'Y-m-d\TH:i:s\Z';
    const TIMEOUT = 30; // in seconds

    /**
     * @var array $classes The defined classes
     */
    private static $classes = [
        'refundPayment', 'commonRequest', 'paymentRequest', 'queryRequest', 'refundPaymentResponse',
        'refundPaymentResult', 'commonResponse', 'paymentResponse', 'orderResponse', 'extInfo', 'cardResponse',
        'authorizationResponse', 'captureResponse', 'customerResponse', 'billingDetailsResponse',
        'shippingDetailsResponse', 'extraDetailsResponse', 'markResponse', 'threeDSResponse',
        'authenticationRequestData', 'authenticationResultData', 'extraResponse', 'fraudManagementResponse',
        'riskControl', 'riskAnalysis', 'riskAssessments', 'wsResponse', 'capturePayment', 'settlementRequest',
        'capturePaymentResponse', 'capturePaymentResult', 'createTokenFromTransaction',
        'createTokenFromTransactionResponse', 'createTokenFromTransactionResult', 'subscriptionResponse',
        'shoppingCartResponse', 'cartItemInfo', 'reactivateToken', 'reactivateTokenResponse', 'reactivateTokenResult',
        'duplicatePayment', 'orderRequest', 'duplicatePaymentResponse', 'duplicatePaymentResult',
        'verifyThreeDSEnrollment', 'cardRequest', 'techRequest', 'threeDSRequest', 'mpiExtensionRequest',
        'verifyThreeDSEnrollmentResponse', 'verifyThreeDSEnrollmentResult', 'validatePayment',
        'validatePaymentResponse', 'validatePaymentResult', 'cancelPayment', 'cancelPaymentResponse',
        'cancelPaymentResult', 'checkThreeDSAuthentication', 'checkThreeDSAuthenticationResponse',
        'checkThreeDSAuthenticationResult', 'getPaymentUuid', 'legacyTransactionKeyRequest', 'getPaymentUuidResponse',
        'legacyTransactionKeyResult', 'updatePayment', 'updatePaymentResponse', 'updatePaymentResult',
        'updatePaymentDetails', 'shoppingCartRequest', 'updatePaymentDetailsResponse',
        'updatePaymentDetailsResult', 'createPayment', 'customerRequest', 'billingDetailsRequest',
        'shippingDetailsRequest', 'extraDetailsRequest', 'createPaymentResponse', 'createPaymentResult',
        'createSubscription', 'subscriptionRequest', 'createSubscriptionResponse', 'createSubscriptionResult',
        'getSubscriptionDetails', 'getSubscriptionDetailsResponse', 'getSubscriptionDetailsResult', 'tokenResponse',
        'updateSubscription', 'updateSubscriptionResponse', 'updateSubscriptionResult', 'cancelToken',
        'cancelTokenResponse', 'cancelTokenResult', 'createToken', 'createTokenResponse', 'createTokenResult',
        'findPayments', 'findPaymentsResponse', 'findPaymentsResult', 'transactionItem', 'getPaymentDetails',
        'getPaymentDetailsResponse', 'getPaymentDetailsResult', 'updateToken', 'updateTokenResponse',
        'updateTokenResult', 'cancelSubscription', 'cancelSubscriptionResponse', 'cancelSubscriptionResult',
        'getTokenDetails', 'getTokenDetailsResponse', 'getTokenDetailsResult'
    ];

    private $shopId;
    private $mode;
    private $key;

    /**
     * @param string $wsdl The WSDL file to use
     * @param array $options An array of config values
     */
    public function __construct(array $options = [], $wsdl = 'https://paiement.systempay.fr/vads-ws/v5?wsdl')
    {
        foreach (self::$classes as $class) {
            if (!isset($options['classmap'][$class])) {
                $options['classmap'][$class] = __NAMESPACE__ . '\\' . ucfirst($class);
            }
        }

        $ssl = [];
        if (isset($options['sni.enabled']) && $options['sni.enabled']) {
            $url = parse_url($wsdl);
            $ssl = ['SNI_enabled' => true, 'SNI_server_name' => $url['host']];

            unset($options['sni.enabled']);
        }

        $options = array_merge([
            'trace' => true,
            'exceptions' => true,
            'soapaction' => '',
            'cache_wsdl' => WSDL_CACHE_NONE,
            'features' => SOAP_SINGLE_ELEMENT_ARRAYS,
            'connection_timeout' => self::TIMEOUT,
            'encoding' => 'UTF-8',
            'soap_version' => SOAP_1_2,
            'stream_context' => stream_context_create(
                ['ssl' => $ssl, 'http' => ['user_agent' => 'PHPSoapClient']]
            )
        ], $options);

        parent::__construct($wsdl, $options);
    }

    public function init($shopId, $mode, $keyTest, $keyProd)
    {
        $this->mode = $mode;
        $this->shopId = $shopId;
        $this->key = ($mode === 'PRODUCTION') ? $keyProd : $keyTest;
    }

    public function getAuthToken($data1, $data2)
    {
        $authToken = base64_encode(hash_hmac('sha256', $data1 . $data2, $this->key, true));
        return $authToken;
    }

    public function genUuid()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0xffff)
        );
    }

    public function setHeaders()
    {
        $this->__setSoapHeaders(null);

        $requestId = $this->genUuid();
        $timestamp = gmdate(self::DATE_FORMAT);
        $authToken = $this->getAuthToken($requestId, $timestamp);

        // create headers for shopId, requestId, timestamp, mode and authToken
        $headers = [];

        $headers[] = new \SOAPHeader(self::HEADER_NAMESPACE, 'shopId', $this->shopId);
        $headers[] = new \SOAPHeader(self::HEADER_NAMESPACE, 'requestId', $requestId);
        $headers[] = new \SOAPHeader(self::HEADER_NAMESPACE, 'timestamp', $timestamp);
        $headers[] = new \SOAPHeader(self::HEADER_NAMESPACE, 'mode', $this->mode);
        $headers[] = new \SOAPHeader(self::HEADER_NAMESPACE, 'authToken', $authToken);

        // set headers to soap client
        $this->__setSoapHeaders($headers);

        return $requestId;
    }

    public function getJsessionId()
    {
        // retrieve header of the last response
        $header = $this->__getLastResponseHeaders();

        $matches = [];
        if (!preg_match('#JSESSIONID=([A-Za-z0-9\._]+)#', $header, $matches)) {
            // no session created by platform
            throw new \SoapFault('SystempaySID', 'No session ID returned by platform.' . $header);
        }

        return $matches[1];
    }

    public function setJsessionId($sid)
    {
        $this->__setCookie('JSESSIONID', $sid);
    }

    public function checkAuthenticity()
    {
        // retrieve SOAP header to check response authenticity
        $dom = new \DOMDocument();
        $dom->loadXML($this->__getLastResponse(), LIBXML_NOWARNING);

        $path = new \DOMXPath($dom);
        $xmlHeaders = $path->query('//*[local-name()="Header"]/*');

        $headers = [];
        foreach ($xmlHeaders as $xmlHeader) {
            $headers[$xmlHeader->nodeName] = $xmlHeader->nodeValue;
        }

        if ($this->shopId !== $headers['shopId']) {
            throw new SecurityException("Inconsistent returned shopId {$headers['shopId']}.");
        }

        if ($this->mode !== $headers['mode']) {
            throw new SecurityException("Inconsistent returned mode {$headers['mode']}.");
        }

        $authToken = $this->getAuthToken($headers['timestamp'], $headers['requestId']);
        if ($authToken !== $headers['authToken']) {
            throw new SecurityException('Authentication failed.');
        }
    }

    public function checkResult(CommonResponse $commonResponse, array $expectedStatuses = [])
    {
        if ($commonResponse->getResponseCode() !== 0) {
            throw new ResultException(
                $commonResponse->getResponseCodeDetail(),
                $commonResponse->getResponseCode()
            );
        }

        if (!empty($expectedStatuses)
            && !in_array($commonResponse->getTransactionStatusLabel(), $expectedStatuses)) {
            throw new ResultException(
                "Unexpected transaction status returned ({$commonResponse->getTransactionStatusLabel()})."
            );
        }
    }

    /**
     * @param RefundPayment $parameters
     * @return RefundPaymentResponse
     */
    public function refundPayment(RefundPayment $parameters)
    {
        return $this->__soapCall('refundPayment', [$parameters]);
    }

    /**
     * @param CapturePayment $parameters
     * @return CapturePaymentResponse
     */
    public function capturePayment(CapturePayment $parameters)
    {
        return $this->__soapCall('capturePayment', [$parameters]);
    }

    /**
     * @param CreateTokenFromTransaction $parameters
     * @return CreateTokenFromTransactionResponse
     */
    public function createTokenFromTransaction(CreateTokenFromTransaction $parameters)
    {
        return $this->__soapCall('createTokenFromTransaction', [$parameters]);
    }

    /**
     * @param ReactivateToken $parameters
     * @return ReactivateTokenResponse
     */
    public function reactivateToken(ReactivateToken $parameters)
    {
        return $this->__soapCall('reactivateToken', [$parameters]);
    }

    /**
     * @param DuplicatePayment $parameters
     * @return DuplicatePaymentResponse
     */
    public function duplicatePayment(DuplicatePayment $parameters)
    {
        return $this->__soapCall('duplicatePayment', [$parameters]);
    }

    /**
     * @param VerifyThreeDSEnrollment $parameters
     * @return VerifyThreeDSEnrollmentResponse
     */
    public function verifyThreeDSEnrollment(VerifyThreeDSEnrollment $parameters)
    {
        return $this->__soapCall('verifyThreeDSEnrollment', [$parameters]);
    }

    /**
     * @param ValidatePayment $parameters
     * @return ValidatePaymentResponse
     */
    public function validatePayment(ValidatePayment $parameters)
    {
        return $this->__soapCall('validatePayment', [$parameters]);
    }

    /**
     * @param CancelPayment $parameters
     * @return CancelPaymentResponse
     */
    public function cancelPayment(CancelPayment $parameters)
    {
        return $this->__soapCall('cancelPayment', [$parameters]);
    }

    /**
     * @param CheckThreeDSAuthentication $parameters
     * @return CheckThreeDSAuthenticationResponse
     */
    public function checkThreeDSAuthentication(CheckThreeDSAuthentication $parameters)
    {
        return $this->__soapCall('checkThreeDSAuthentication', [$parameters]);
    }

    /**
     * @param GetPaymentUuid $parameters
     * @return GetPaymentUuidResponse
     */
    public function getPaymentUuid(GetPaymentUuid $parameters)
    {
        return $this->__soapCall('getPaymentUuid', [$parameters]);
    }

    /**
     * @param UpdatePayment $parameters
     * @return UpdatePaymentResponse
     */
    public function updatePayment(UpdatePayment $parameters)
    {
        return $this->__soapCall('updatePayment', [$parameters]);
    }

    /**
     * @param UpdatePaymentDetails $parameters
     * @return UpdatePaymentDetailsResponse
     */
    public function updatePaymentDetails(UpdatePaymentDetails $parameters)
    {
        return $this->__soapCall('updatePaymentDetails', [$parameters]);
    }

    /**
     * @param CreatePayment $parameters
     * @return CreatePaymentResponse
     */
    public function createPayment(CreatePayment $parameters)
    {
        return $this->__soapCall('createPayment', [$parameters]);
    }

    /**
     * @param CreateSubscription $parameters
     * @return CreateSubscriptionResponse
     */
    public function createSubscription(CreateSubscription $parameters)
    {
        return $this->__soapCall('createSubscription', [$parameters]);
    }

    /**
     * @param GetSubscriptionDetails $parameters
     * @return GetSubscriptionDetailsResponse
     */
    public function getSubscriptionDetails(GetSubscriptionDetails $parameters)
    {
        return $this->__soapCall('getSubscriptionDetails', [$parameters]);
    }

    /**
     * @param UpdateSubscription $parameters
     * @return UpdateSubscriptionResponse
     */
    public function updateSubscription(UpdateSubscription $parameters)
    {
        return $this->__soapCall('updateSubscription', [$parameters]);
    }

    /**
     * @param CancelToken $parameters
     * @return CancelTokenResponse
     */
    public function cancelToken(CancelToken $parameters)
    {
        return $this->__soapCall('cancelToken', [$parameters]);
    }

    /**
     * @param CreateToken $parameters
     * @return CreateTokenResponse
     */
    public function createToken(CreateToken $parameters)
    {
        return $this->__soapCall('createToken', [$parameters]);
    }

    /**
     * @param FindPayments $parameters
     * @return FindPaymentsResponse
     */
    public function findPayments(FindPayments $parameters)
    {
        return $this->__soapCall('findPayments', [$parameters]);
    }

    /**
     * @param GetPaymentDetails $parameters
     * @return GetPaymentDetailsResponse
     */
    public function getPaymentDetails(GetPaymentDetails $parameters)
    {
        return $this->__soapCall('getPaymentDetails', [$parameters]);
    }

    /**
     * @param UpdateToken $parameters
     * @return UpdateTokenResponse
     */
    public function updateToken(UpdateToken $parameters)
    {
        return $this->__soapCall('updateToken', [$parameters]);
    }

    /**
     * @param CancelSubscription $parameters
     * @return CancelSubscriptionResponse
     */
    public function cancelSubscription(CancelSubscription $parameters)
    {
        return $this->__soapCall('cancelSubscription', [$parameters]);
    }

    /**
     * @param GetTokenDetails $parameters
     * @return GetTokenDetailsResponse
     */
    public function getTokenDetails(GetTokenDetails $parameters)
    {
        return $this->__soapCall('getTokenDetails', [$parameters]);
    }
}
