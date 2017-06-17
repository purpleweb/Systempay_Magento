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

class Lyra_Systempay_Block_Oney extends Lyra_Systempay_Block_Abstract
{
    protected $_model = 'oney';

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('systempay/oney.phtml');
    }

    public function getPaymentOptions()
    {
        if ($this->_getModel()->getConfigData('enable_payment_options') != 1) {
            // local payment options selection is not allowed
            return false;
        }

        $amount = $this->getMethod()->getInfoInstance()->getQuote()->getBaseGrandTotal();
        return $this->_getModel()->getPaymentOptions($amount);
    }

    public function getHtmlReview(array $option, $first)
    {
        $quote = $this->getMethod()->getInfoInstance()->getQuote();
        $amount = $quote->getGrandTotal();

        $block = $this->getLayout()->createBlock('systempay/oney_review')
                                    ->setOption($option)
                                    ->setAmount($amount)
                                    ->setFirst($first);

        return $block->toHtml();
    }
}
