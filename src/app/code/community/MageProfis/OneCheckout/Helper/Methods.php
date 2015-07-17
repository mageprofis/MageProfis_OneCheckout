<?php

/**
 *
 * @category   MageProfis
 * @package    MageProfis_OneCheckout
 * @copyright  Copyright (c) 2015 Ulrich Abelmann
 * @copyright  Copyright (c) 2015 MageProfis GmbH
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MageProfis_OneCheckout_Helper_Methods
extends Mage_Core_Helper_Abstract
{
    /**
     * @return Mage_Checkout_Model_Type_Onepage
     */
    protected function getCheckout()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

    /**
     * 
     * @return Mage_Sales_Model_Quote
     */
    protected function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    /**
     * get Shipping Methods
     * 
     * @return array
     */
    public function getShipping()
    {
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
            foreach ($groups as $c => $group) {
                foreach ($group as $method) {
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

    /**
     * get First Shipping Method
     * 
     * @return boolean|Varien_Object
     */
    public function getFirstShipping()
    {
        $methods = $this->getShipping();
        foreach ($methods as $code => $method) {
            return $code;
        }
        return false;
    }

    /**
     * get Payment Methods
     * 
     * @return array
     */
    public function getPayment()
    {
        $quote = $this->getQuote();
        $store = $quote ? $quote->getStoreId() : null;
        $methods = Mage::helper('payment')->getStoreMethods($store, $quote);
        $total = $quote->getBaseSubtotal();
        foreach ($methods as $key => $method) {
            if ($this->_canUsePaymentMethod($method) && ($total != 0 || $method->getCode() == 'free' || ($quote->hasRecurringItems() && $method->canManageRecurringProfiles()))) {
                $method->setInfoInstance($quote->getPayment());
            } else {
                unset($methods[$key]);
            }
        }
        Mage::getModel("customer/session")->setOneCheckoutKey(count($methods));
        return $methods;
    }

    /**
     * get First Payment Method
     * 
     * @return boolean|Varien_Object
     */
    public function getFirstPayment()
    {
        $methods = $this->getPayment();
        foreach ($methods as $method) {
            return $method->getCode();
        }
        return false;
    }

    /**
     * can Use Payment Method
     * 
     * @param string $method
     * @return boolean
     */
    protected function _canUsePaymentMethod($method)
    {
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

        if ((!empty($minTotal) && ($total < $minTotal)) || (!empty($maxTotal) && ($total > $maxTotal))) {
            return false;
        }
        return true;
    }

}
