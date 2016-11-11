<?php
/**
 *
 * @category   MageProfis
 * @package    MageProfis_OneCheckout
 * @copyright  Copyright (c) 2015 Ulrich Abelmann
 * @copyright  Copyright (c) 2015 MageProfis GmbH
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MageProfis_OneCheckout_Block_Checkout
extends Mage_Core_Block_Template
{
    /**
     * add automaticly additional data
     * 
     * @return string
     */
    protected function _toHtml()
    {
        return parent::_toHtml()
                . $this->getChildHtml('additional.data');
    }

    /**
     * Retrieve checkout session model
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Retrieve quote session model
     * 
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckout()->getQuote();
    }

    /**
     * check if Shipping Adress is Same as Billing Adress
     * 
     * @return boolean
     */
    public function getShippingIsSameAsBilling()
    {
        $shippingBlock = $this->getChild('shipping');
        /* @var $shippingBlock Mage_Checkout_Block_Onepage_Shipping */
        if ($shippingBlock instanceof Mage_Checkout_Block_Onepage_Shipping)
        {
            $same_as_billing = $shippingBlock->getAddress()->getSameAsBilling();
            // if address is not set return true
            if (is_null($same_as_billing))
            {
                return true;
            }
            return (int) $same_as_billing;
        }
        return false;
    }
    
    /**
     * Retrieve is allow and show block
     *
     * @return bool
     */
    public function isShowShipping()
    {
        return !$this->getQuote()->isVirtual();
    }

    /**
     * is Customer Logged in
     * 
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    /**
     * Show Edit Cart Button on Checkout
     * 
     * @return bool
     */
    public function showEditCartButton()
    {
        return Mage::getStoreConfigFlag('onecheckout/general/showcarteditionbutton');
    }

    /**
     * get Ajax / Save Url
     * 
     * @return string
     */
    public function getAjaxSaveUrl()
    {
        return Mage::getUrl('onecheckout/ajax/update',array('_secure'=>Mage::app()->getStore()->isCurrentlySecure()));
    }

    /**
     * get Ajax / Review Url
     * 
     * @return string
     */
    public function getAjaxReviewUrl()
    {
        return Mage::getUrl('onecheckout/ajax/review',array('_secure'=>Mage::app()->getStore()->isCurrentlySecure()));
    }

    /**
     * get Failure Url
     * 
     * @return string
     */
    public function getFailureUrl()
    {
        return Mage::getUrl('checkout/cart');
    }

    /**
     * get Complete Url 
     * 
     * @return string
     */
    public function getCompleteUrl()
    {
        return Mage::getUrl('checkout/onepage/saveOrder', array('form_key' => Mage::getSingleton('core/session')->getFormKey(),'_secure'=>Mage::app()->getStore()->isCurrentlySecure()));
    }

    /**
     * get Success Url
     * 
     * @return string
     */
    public function getSuccessUrl()
    {
        return Mage::getUrl('checkout/onepage/success',array('_secure'=>Mage::app()->getStore()->isCurrentlySecure()));
    }

    /**
     * get pre Login Url
     * 
     * @return string
     */
    public function getPreLoginUrl()
    {
        return Mage::getUrl('onecheckout/ajax/preLogin',array('_secure'=>Mage::app()->getStore()->isCurrentlySecure()));
    }
}
