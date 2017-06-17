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

class Lyra_Systempay_Model_Field_Gift_AddedCards extends Lyra_Systempay_Model_Field_Array
{
    protected $_eventPrefix = 'systempay_field_gift_added_cards';

    /**
     * Save uploaded files before saving config value
     */
    protected function _beforeSave()
    {
        $uploadDir = Mage::getBaseDir('media') . DS . 'systempay' . DS . 'gift' . DS;

        $data = Mage::app()->getRequest()->getParam('groups');
        $cards = $data[$this->getGroupId()]['fields'][$this->getField()]['value'];

        if (!is_array($cards) || empty($cards)) {
            $this->setValue(array());
        } else {
            $i = 0;
            foreach ($cards as $key => $card) {
                $i++;

                if (empty($card)) {
                    continue;
                }

                if (empty($card['code']) || !preg_match('#^[A-Za-z0-9\-_]+$#', $card['code'])) {
                    $this->_throwError('Card code', $i);
                }
                if (!preg_match('#^[^<>]*$#u', $card['name'])) {
                    $this->_throwError('Card name', $i);
                }

                // load latest logo value
                if (file_exists($uploadDir . strtolower($card['code'] . '.png'))) {
                    $cards[$key]['logo'] = strtolower($card['code'] . '.png');
                }

                // process file upload
                if (is_array($_FILES) && !empty($_FILES)) {
                    $file = array();
                    $file['tmp_name'] = $_FILES['groups']['tmp_name'][$this->getGroupId()]['fields'][$this->getField()]['value'][$key]['logo'];
                    $file['name'] = $_FILES['groups']['name'][$this->getGroupId()]['fields'][$this->getField()]['value'][$key]['logo'];

                    if ($file['tmp_name'] && $file['name']) { // is there any file uploaded for the current card
                        try {
                            $uploader = new Varien_File_Uploader($file);
                            $uploader->setAllowedExtensions(array('png'));
                            $uploader->setAllowRenameFiles(false);

                            $result = $uploader->save($uploadDir, strtolower($card['code'] . '.png'));

                            if (key_exists('file', $result) && !empty($result['file'])) {
                                $cards[$key]['logo'] = $result['file'];
                            }
                        } catch (Exception $e) {
                            // upload errors
                            $this->_throwError('Card logo', $i, $e->getMessage());
                        }
                    }
                }
            }

            $this->setValue($cards);
        }

        return parent::_beforeSave();
    }
}
