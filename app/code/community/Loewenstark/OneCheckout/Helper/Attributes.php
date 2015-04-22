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

class Loewenstark_OneCheckout_Helper_Attributes extends Mage_Core_Helper_Abstract {

	protected $_finished = array();

    private function getProduct($item) {
		$productId = $item->getProductId();
        return Mage::getModel('catalog/product')->load($productId);
    }
	
	private function getExclude() {
		$list = array();
		foreach (explode("\n", Mage::getStoreConfig("onecheckout/attributes/exclude")) as $word) {
			$word = trim($word);
			if ($word != "") {
				$list[] = Mage::helper('core')->escapeHtml($word, null);
			}
		}
		return $list;
	}
	
	public function getAttributes($item) {
		if (Mage::getStoreConfig("onecheckout/attributes/show") != "1") {
			return false;
		}
	
		$itemId = $item->getId();
		
		if (!isset($this->_finished[$itemId])) {
			$this->_finished[$itemId] = true;
			
			$product = $this->getProduct($item);
		
			$attributes = $this->getAdditionalData($product);
			if (count($attributes) > 0) {
				return $attributes;
			}
		}
		
		return false;
	}
	

    private function getAdditionalData($product)
    {
        $data = array();
		$excludeAttr = $this->getExclude();
        $attributes = $product->getAttributes();
        foreach ($attributes as $attribute) {
//            if ($attribute->getIsVisibleOnFront() && $attribute->getIsUserDefined() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
            if ($attribute->getIsVisibleOnFront() && !in_array($attribute->getAttributeCode(), $excludeAttr)) {
                $value = $attribute->getFrontend()->getValue($product);

                if (!$product->hasData($attribute->getAttributeCode())) {
                    $value = Mage::helper('catalog')->__('N/A');
                } elseif ((string)$value == '') {
                    $value = Mage::helper('catalog')->__('No');
                } elseif ($attribute->getFrontendInput() == 'price' && is_string($value)) {
                    $value = Mage::app()->getStore()->convertPrice($value, true);
                }

                if (is_string($value) && strlen($value)) {
                    $data[$attribute->getAttributeCode()] = array(
                        'label' => $attribute->getStoreLabel(),
                        'value' => $value,
                        'code'  => $attribute->getAttributeCode()
                    );
                }
            }
        }
        return $data;
    }	
	
}
