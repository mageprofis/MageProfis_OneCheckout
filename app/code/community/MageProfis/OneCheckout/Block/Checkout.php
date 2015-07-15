<?php 
/**
 *
 * @category   MageProfis
 * @package    MageProfis_OneCheckout
 * @copyright  Copyright (c) 2015 Ulrich Abelmann
 * @copyright  Copyright (c) 2015 MageProfis GmbH
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MageProfis_OneCheckout_Block_Checkout extends Mage_Core_Block_Template {


    public function isCustomerLoggedIn() {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }
}
