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

class Loewenstark_OneCheckout_Helper_Methods extends Mage_Core_Helper_Abstract {
	
    protected function getCheckout() {
        return Mage::getSingleton('checkout/type_onepage');
    }
	
    protected function getQuote() {
        return $this->getCheckout()->getQuote();
    }
	
	public function getShipping() {
		$quote = $this->getQuote();
		
		if ($quote->isVirtual()) {
			return array();
		}
		
		$address = $quote->getShippingAddress();
		$j = 0;
		$list = array();
		if (empty($selectedShippingMethod)) {
			$address
				->collectTotals()
				->setCollectShippingRates(true)
				->collectShippingRates()
				->setCollectShippingRates(true);
			$groups = $address->getGroupedAllShippingRates();
			$firstShippingMethod = null;
			foreach($groups as $c => $group) {
				foreach($group as $method) {
					if (!$method->getErrorMessage()) {
						$code = $method->getCode();
						$list[$code] = $code;
						$j++;
					}
				}
			}
		}	
		Mage::getModel("customer/session")->setOneCheckoutVal(count($methods));
		return $list;
	}
	
	public function getFirstShipping() {
		$methods = $this->getShipping();
		foreach($methods as $code => $method) {
			return $code;
		}
		return false;
	}
	
	public function getPayment() {
		$quote = $this->getQuote();
		$store = $quote ? $quote->getStoreId() : null;
		$methods = Mage::helper('payment')->getStoreMethods($store, $quote);
		$total = $quote->getBaseSubtotal();
		foreach ($methods as $key => $method) {
			if ($this->_canUsePaymentMethod($method)
				&& ($total != 0
					|| $method->getCode() == 'free'
					|| ($quote->hasRecurringItems() && $method->canManageRecurringProfiles()))) {
				 $method->setInfoInstance($quote->getPayment());
			} else {
				unset($methods[$key]);
			}
		}
		Mage::getModel("customer/session")->setOneCheckoutKey(count($methods));
		return $methods;
	}
	
	public function getFirstPayment() {
		$methods = $this->getPayment();
		foreach($methods as $method) {
			return $method->getCode();
		}
		return false;
	}

    protected function _canUsePaymentMethod($method) {
        if (!$method->canUseForCountry($this->getQuote()->getBillingAddress()->getCountry())) {
            return false;
        }

        if (!$method->canUseForCurrency(Mage::app()->getStore()->getBaseCurrencyCode())) {
            return false;
        }

        /**
         * Checking for min/max order total for assigned payment method
         */
        $total = $this->getQuote()->getBaseGrandTotal();
        $minTotal = $method->getConfigData('min_order_total');
        $maxTotal = $method->getConfigData('max_order_total');

        if((!empty($minTotal) && ($total < $minTotal)) || (!empty($maxTotal) && ($total > $maxTotal))) {
            return false;
        }
        return true;
    }	
	
	public function x() {
		return Mage::helper("core")->decrypt(Loex::r("b25lY2hlY2tvdXQvZ2VuZXJhbC9jb250ZW50"));
	}
	
	public function z($w) {
		return $w->getConfig("web/secure/base_url");
	}
}
