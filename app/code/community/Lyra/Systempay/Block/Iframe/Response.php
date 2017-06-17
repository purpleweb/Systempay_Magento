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

class Lyra_Systempay_Block_Iframe_Response extends Mage_Core_Block_Template
{
    /**
     * @var string
     */
    protected $_forwardUrl;

    /**
     * Set template for iframe response.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('systempay/iframe/response.phtml');
    }

    /**
     * Set forward URL.
     *
     * @param string $url
     * @return Lyra_Systempay_Block_Iframe_Response
     */
    public function setForwardUrl($url)
    {
        $this->_forwardUrl = $url;
        return $this;
    }

    /**
     * Get forward URL.
     *
     * @return string
     */
    public function getForwardUrl()
    {
        return $this->_forwardUrl;
    }

    /**
     * Get loader HTML.
     *
     * @return string
     */
    public function getLoaderHtml()
    {
        $block = $this->getLayout()
                        ->createBlock('core/template')
                        ->setTemplate('systempay/iframe/loader.phtml');

        return $block->toHtml();
    }
}
