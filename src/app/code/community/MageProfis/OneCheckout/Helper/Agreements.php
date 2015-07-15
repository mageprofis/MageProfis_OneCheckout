<?php

/**
 *
 * @category   MageProfis
 * @package    MageProfis_OneCheckout
 * @copyright  Copyright (c) 2015 Ulrich Abelmann
 * @copyright  Copyright (c) 2015 MageProfis GmbH
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MageProfis_OneCheckout_Helper_Agreements extends Mage_Core_Helper_Abstract
{

    protected function getKeywords()
    {
        $list = array();
        foreach (explode("\n", Mage::getStoreConfig("onecheckout/agreements/keywords")) as $word) {
            $word = trim($word);
            if ($word != "") {
                $list[] = Mage::helper('core')->escapeHtml($word, null);
            }
        }
        return $list;
    }

    public function getLinkedText($agreement)
    {
        $text = Mage::helper('core')->escapeHtml($agreement->getCheckboxText(), null);

        foreach ($this->getKeywords() as $word) {
            $text = str_replace($word, "<a href=\"#\" onclick=\"\$('agreement-content-" . $agreement->getId() . "').toggle(); return false;\">$word</a>", $text);
        }
        return $text;
    }

}
