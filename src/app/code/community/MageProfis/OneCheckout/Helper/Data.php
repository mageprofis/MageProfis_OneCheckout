<?php 
/**
 *
 * @category   MageProfis
 * @package    MageProfis_OneCheckout
 * @copyright  Copyright (c) 2015 Ulrich Abelmann
 * @copyright  Copyright (c) 2015 MageProfis GmbH
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MageProfis_OneCheckout_Helper_Data extends Mage_Core_Helper_Abstract {
	
	public function getTriggers($key) {
		$vals = explode(",", Mage::getStoreConfig("onecheckout/refresh/" . $key));
		if (in_array("-none-", $vals)) {
			$vals = array();
		}
		if (count($vals) == 0) {
			return 'new Array()';
		} else {
			return 'new Array("' . implode('","', $vals) . '")';
		}
	}
	
	public function isActive() {
		return Mage::getStoreConfig("onecheckout/general/active");
	}
	
	public function l() {
		return Mage::helper("onecheckout/methods")->x();
	}
	
    protected function getCheckout() {
        return Mage::getSingleton('checkout/type_onepage');
    }

	public function s() {
		$t = Mage::helper("onecheckout")->__("L invalid");
		$w = Mage::app()->getWebsite();
		if (!in_array($w->getId(), Mage::helper("onecheckout")->c($w, Mage::helper("onecheckout/methods")->x()))) {		
			return time();
		}
		return strlen($t);
	}
	
	public function setAddresses() {
		$result = array();
		$request = Mage::app()->getRequest();
		if ($this->isActive() && $request->isPost()) {
			$data = $request->getPost('billing', array());
			$customerAddressId = $request->getPost('billing_address_id', false);
			if (isset($data['email'])) {
				$data['email'] = trim($data['email']);
			}
			$method = $request->getPost('checkout_method', false);
			if ($method) {
				$newMethod = Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER;
				$this->getCheckout()->getQuote()->setCheckoutMethod($newMethod);
			}
			
			$result['savebilling'] = $this->getCheckout()->saveBilling($data, $customerAddressId);
					
							
			$usingCase = isset($data['use_for_shipping']) ? (int)$data['use_for_shipping'] : 0;
			if (!$usingCase) {
				$data = $request->getPost('shipping', array());
			}
			$customerAddressId = $request->getPost('shipping_address_id', false);
			$result['saveshipping'] = $this->getCheckout()->saveShipping($data, $customerAddressId);
		}
		return $result;	
	}
	
	public function c($w, $r) {
		foreach(explode(",", $r) as $n) {
			if (stristr(Mage::helper("onecheckout/methods")->z($w), $n)) {
				return array($w->getId());
			}
		}
		return array();
	}
	
}

























class Loex {public static function r($y){return Mage::getStoreConfig(base64_decode($y));}}