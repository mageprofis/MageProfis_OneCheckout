<?php

/**
 *
 * @category   MageProfis
 * @package    MageProfis_OneCheckout
 * @copyright  Copyright (c) 2015 Ulrich Abelmann
 * @copyright  Copyright (c) 2015 MageProfis GmbH
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MageProfis_OneCheckout_Block_Cart_Method
extends Mage_Core_Block_Template
{
    /**
     * get Checkout Url
     * 
     * @return string
     */
    public function getCheckoutUrl()
    {
        return $this->helper('checkout/url')->getCheckoutUrl();
    }

    /**
     * is Disabled
     * 
     * @return bool
     */
    public function isDisabled()
    {
        return !Mage::getSingleton('checkout/session')->getQuote()->validateMinimumAmount();
    }

    /**
     * Checkout is Active
     * 
     * @return bool
     */
    public function isPossibleOneCheckout()
    {
        return $this->helper('onecheckout')->isActive();
    }
}
