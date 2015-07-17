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
     * is Customer Logged in
     * 
     * @return bool
     */
    public function isCustomerLoggedIn()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    /**
     * 
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
        return Mage::getUrl('onecheckout/ajax/update');
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
        return Mage::getUrl('checkout/onepage/saveOrder', array('form_key' => Mage::getSingleton('core/session')->getFormKey()));
    }

    /**
     * get Success Url
     * 
     * @return string
     */
    public function getSuccessUrl()
    {
        return Mage::getUrl('checkout/onepage/success');
    }

    /**
     * get pre Login Url
     * 
     * @return string
     */
    public function getPreLoginUrl()
    {
        return Mage::getUrl('onecheckout/ajax/preLogin');
    }
}
