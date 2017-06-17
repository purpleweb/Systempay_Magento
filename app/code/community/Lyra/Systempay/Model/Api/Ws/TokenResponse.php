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

class TokenResponse
{
    /**
     * @var \DateTime $creationDate
     */
    private $creationDate = null;

    /**
     * @var \DateTime $cancellationDate
     */
    private $cancellationDate = null;

    /**
     * @return \DateTime
     */
    public function getCreationDate()
    {
        if ($this->creationDate == null) {
            return null;
        } else {
            try {
                return \DateTime::createFromFormat(\DateTime::ATOM, $this->creationDate);
            } catch (\Exception $e) {
                return false;
            }
        }
    }

    /**
     * @param \DateTime $creationDate
     * @return TokenResponse
     */
    public function setCreationDate(\DateTime $creationDate = null)
    {
        if ($creationDate == null) {
            $this->creationDate = null;
        } else {
            $this->creationDate = $creationDate->format(\DateTime::ATOM);
        }
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getCancellationDate()
    {
        if ($this->cancellationDate == null) {
            return null;
        } else {
            try {
                return \DateTime::createFromFormat(\DateTime::ATOM, $this->cancellationDate);
            } catch (\Exception $e) {
                return false;
            }
        }
    }

    /**
     * @param \DateTime $cancellationDate
     * @return TokenResponse
     */
    public function setCancellationDate(\DateTime $cancellationDate = null)
    {
        if ($cancellationDate == null) {
            $this->cancellationDate = null;
        } else {
            $this->cancellationDate = $cancellationDate->format(\DateTime::ATOM);
        }
        return $this;
    }
}
