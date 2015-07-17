<?php
/**
 *
 * @category   MageProfis
 * @package    MageProfis_OneCheckout
 * @copyright  Copyright (c) 2015 Ulrich Abelmann
 * @copyright  Copyright (c) 2015 MageProfis GmbH
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MageProfis_OneCheckout_Helper_Checkout_Url
extends Mage_Checkout_Helper_Url
{
    /**
     * get Checkout Url
     * 
     * @return string
     */
    public function getCheckoutUrl()
    {
        if (Mage::helper("onecheckout")->isActive()) {
            return $this->_getUrl('onecheckout');
        }

        return parent::getCheckoutUrl();
    }
}
