<?php
/**
 * If FireGento_MageSetup is installed use their Checkout_Agreements
 * block class, otherwise use standard.
 */

// @codingStandardsIgnoreStart
if (Mage::getConfig()->getModuleConfig('FireGento_MageSetup')->is('active', 'true')) {

    abstract class MageProfis_OneCheckout_Block_Checkout_Agreements_Abstract
        extends FireGento_MageSetup_Block_Checkout_Agreements
    {
    }

} else {

    abstract class MageProfis_OneCheckout_Block_Checkout_Agreements_Abstract
        extends Mage_Checkout_Block_Agreements
    {
    }

}
// @codingStandardsIgnoreEnd
