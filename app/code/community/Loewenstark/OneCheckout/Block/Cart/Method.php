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

class Loewenstark_OneCheckout_Block_Cart_Method extends Mage_Core_Block_Template {

    public function getCheckoutUrl() {
        return $this->helper('checkout/url')->getCheckoutUrl();
    }

    public function isDisabled() {
        return !Mage::getSingleton('checkout/session')->getQuote()->validateMinimumAmount();
    }

    public function isPossibleOneCheckout() {
        return $this->helper('onecheckout')->isActive();
    }

}
