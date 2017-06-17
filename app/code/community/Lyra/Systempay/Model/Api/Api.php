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

if (! class_exists('Lyra_Systempay_Model_Api_Api', false)) {

    /**
     * Utility class for managing parameters checking, inetrnationalization, signature building and more.
     */
    class Lyra_Systempay_Model_Api_Api
    {

        /**
         * The list of encodings supported by the API.
         *
         * @var array[string]
         */
        public static $SUPPORTED_ENCODINGS = array(
            'UTF-8',
            'ASCII',
            'Windows-1252',
            'ISO-8859-15',
            'ISO-8859-1',
            'ISO-8859-6',
            'CP1256'
        );

        /**
         * Generate a trans_id.
         * To be independent from shared/persistent counters, we use the number of 1/10 seconds since midnight
         * which has the appropriatee format (000000-899999) and has great chances to be unique.
         *
         * @param int $timestamp
         * @return string the generated trans_id
         */
        public static function generateTransId($timestamp = null)
        {
            if (! $timestamp) {
                $timestamp = time();
            }

            $parts = explode(' ', microtime());
            $id = ($timestamp + $parts[0] - strtotime('today 00:00')) * 10;
            $id = sprintf('%06d', $id);

            return $id;
        }

        /**
         * Returns an array of languages accepted by the Systempay payment platform.
         *
         * @return array[string][string]
         */
        public static function getSupportedLanguages()
        {
            return array(
                'de' => 'German', 'en' => 'English', 'zh' => 'Chinese', 'es' => 'Spanish', 'fr' => 'French',
                'it' => 'Italian', 'ja' => 'Japanese', 'nl' => 'Dutch', 'pl' => 'Polish', 'pt' => 'Portuguese',
                'ru' => 'Russian', 'sv' => 'Swedish', 'tr' => 'Turkish'
            );
        }

        /**
         * Returns true if the entered language (ISO code) is supported.
         *
         * @param string $lang
         * @return boolean
         */
        public static function isSupportedLanguage($lang)
        {
            foreach (array_keys(self::getSupportedLanguages()) as $code) {
                if ($code == strtolower($lang)) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Return the list of currencies recognized by the Systempay platform.
         *
         * @return array[int][Lyra_Systempay_Model_Api_Currency]
         */
        public static function getSupportedCurrencies()
        {
            $currencies = array(
                array('ARS', '032', 2), array('AUD', '036', 2), array('KHR', '116', 0), array('CAD', '124', 2),
                array('CNY', '156', 1), array('HRK', '191', 2), array('CZK', '203', 2), array('DKK', '208', 2),
                array('EKK', '233', 2), array('HKD', '344', 2), array('HUF', '348', 2), array('ISK', '352', 0),
                array('IDR', '360', 0), array('JPY', '392', 0), array('KRW', '410', 0), array('LVL', '428', 2),
                array('LTL', '440', 2), array('MYR', '458', 2), array('MXN', '484', 2), array('NZD', '554', 2),
                array('NOK', '578', 2), array('PHP', '608', 2), array('RUB', '643', 2), array('SGD', '702', 2),
                array('ZAR', '710', 2), array('SEK', '752', 2), array('CHF', '756', 2), array('THB', '764', 2),
                array('GBP', '826', 2), array('USD', '840', 2), array('TWD', '901', 1), array('RON', '946', 2),
                array('TRY', '949', 2), array('XOF', '952', 0), array('BGN', '975', 2), array('EUR', '978', 2),
                array('PLN', '985', 2), array('BRL', '986', 2)
            );

            $systempay_currencies = array();

            foreach ($currencies as $currency) {
                $systempay_currencies[] = new Lyra_Systempay_Model_Api_Currency($currency[0], $currency[1], $currency[2]);
            }

            return $systempay_currencies;
        }

        /**
         * Return a currency from its 3-letters ISO code.
         *
         * @param string $alpha3
         * @return Lyra_Systempay_Model_Api_Currency
         */
        public static function findCurrencyByAlphaCode($alpha3)
        {
            $list = self::getSupportedCurrencies();
            foreach ($list as $currency) {
                /**
                 * @var Lyra_Systempay_Model_Api_Currency $currency
                 */
                if ($currency->getAlpha3() == $alpha3) {
                    return $currency;
                }
            }

            return null;
        }

        /**
         * Returns a currency form its numeric ISO code.
         *
         * @param int $numeric
         * @return Lyra_Systempay_Model_Api_Currency
         */
        public static function findCurrencyByNumCode($numeric)
        {
            $list = self::getSupportedCurrencies();
            foreach ($list as $currency) {
                /**
                 * @var Lyra_Systempay_Model_Api_Currency $currency
                 */
                if ($currency->getNum() == $numeric) {
                    return $currency;
                }
            }

            return null;
        }

        /**
         * Return a currency from its 3-letters or numeric ISO code.
         *
         * @param string $code
         * @return Lyra_Systempay_Model_Api_Currency
         */
        public static function findCurrency($code)
        {
            $list = self::getSupportedCurrencies();
            foreach ($list as $currency) {
                /**
                 * @var Lyra_Systempay_Model_Api_Currency $currency
                 */
                if ($currency->getNum() == $code || $currency->getAlpha3() == $code) {
                    return $currency;
                }
            }

            return null;
        }

        /**
         * Returns currency numeric ISO code from its 3-letters code.
         *
         * @param string $alpha3
         * @return int
         */
        public static function getCurrencyNumCode($alpha3)
        {
            $currency = self::findCurrencyByAlphaCode($alpha3);
            return ($currency instanceof Lyra_Systempay_Model_Api_Currency) ? $currency->getNum() : null;
        }

        /**
         * Returns an array of card types accepted by the Systempay payment platform.
         *
         * @return array[string][string]
         */
        public static function getSupportedCardTypes()
        {
            return array(
                'CB' => 'CB', 'E-CARTEBLEUE' => 'E-Carte bleue', 'MAESTRO' => 'Maestro', 'MASTERCARD' => 'MasterCard',
                'VISA' => 'Visa', 'VISA_ELECTRON' => 'Visa Electron', 'AMEX' => 'American Express',
                'AURORE-MULTI' => 'Carte Aurore (Multi enseigne)', 'COFINOGA' => 'Cofinoga', 'DINERS' => 'Carte Diners Club',
                'E_CV' => 'E-chèque vacance', 'IDEAL' => 'iDEAL virement bancaire', 'JCB' => 'Carte JCB',
                'KLARNA' => 'Paiement par facture', 'ONEY' => 'Paiement en 3/4 fois Oney FacilyPay',
                'ONEY_SANDBOX' => 'Paiement en 3/4 fois Oney FacilyPay - Sandbox', 'PAYPAL' => 'PayPal',
                'PAYPAL_SB' => 'PayPal - Sandbox', 'POSTFINANCE' => 'PostFinance', 'SOFORT_BANKING' => 'Sofort'
            );
        }

        /**
         * Compute a Systempay signature. Parameters must be in UTF-8.
         *
         * @param array[string][string] $parameters payment platform request/response parameters
         * @param string $key shop certificate
         * @param boolean $hashed set to false to get the unhashed signature
         * @return string
         */
        public static function sign($parameters, $key, $hashed = true)
        {
            ksort($parameters);

            $sign = '';
            foreach ($parameters as $name => $value) {
                if (substr($name, 0, 5) == 'vads_') {
                    $sign .= $value . '+';
                }
            }

            $sign .= $key;
            return $hashed ? sha1($sign) : $sign;
        }

        /**
         * PHP is not yet a sufficiently advanced technology to be indistinguishable from magic...
         * so don't use magic_quotes, they mess up with the platform response analysis.
         *
         * @param array $potentially_quoted_data
         * @return mixed
         */
        public static function uncharm($potentially_quoted_data)
        {
            if (get_magic_quotes_gpc()) {
                $sane = array();
                foreach ($potentially_quoted_data as $k => $v) {
                    $sane_key = stripslashes($k);
                    $sane_value = is_array($v) ? self::uncharm($v) : stripslashes($v);
                    $sane[$sane_key] = $sane_value;
                }
            } else {
                $sane = $potentially_quoted_data;
            }

            return $sane;
        }
    }
}
