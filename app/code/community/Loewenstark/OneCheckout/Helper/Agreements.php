<?php 

/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Loewenstark Magento License (LML 1.0).
 * It is  available through the world-wide-web at this URL:
 * http://www.loewenstark.de/licenses/lml-1.0.html
 * If you are unable to obtain it through the world-wide-web, please send an 
 * email to license@loewenstark.de so we can send you a copy immediately.
 *
 * @category   Loewenstark
 * @package    Loewenstark_OneCheckout
 * @copyright  Copyright (c) 2012 Ulrich Abelmann
 * @copyright  Copyright (c) 2012 wwg.löwenstark im Internet GmbH
 * @license    http://www.loewenstark.de/licenses/lml-1.0.html  Loewenstark Magento License (LML 1.0)
 */

class Loewenstark_OneCheckout_Helper_Agreements extends Mage_Core_Helper_Abstract {
	
	private function getKeywords() {
		$list = array();
		foreach (explode("\n", Mage::getStoreConfig("onecheckout/agreements/keywords")) as $word) {
			$word = trim($word);
			if ($word != "") {
				$list[] = Mage::helper('core')->escapeHtml($word, null);
			}
		}
		return $list;
	}
	
	public function getLinkedText($agreement) {
		$text = Mage::helper('core')->escapeHtml($agreement->getCheckboxText(), null);
	
		foreach($this->getKeywords() as $word) {
			$text = str_replace($word, "<a href=\"#\" onclick=\"\$('agreement-content-" . $agreement->getId() . "').toggle(); return false;\">$word</a>", $text);
		}
		return $text;
	}
	
}
