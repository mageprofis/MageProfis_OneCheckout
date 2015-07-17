<?php
/**
 *
 * @category   MageProfis
 * @package    MageProfis_OneCheckout
 * @copyright  Copyright (c) 2015 Ulrich Abelmann
 * @copyright  Copyright (c) 2015 MageProfis GmbH
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MageProfis_OneCheckout_Model_Observer extends Mage_Core_Model_Abstract
{
    /**
     * 
     * @param Varien_Object $observer
     */
    public function redirect($observer)
    {
        if (Mage::helper('onecheckout')->isActive())
        {
            $url = Mage::helper('onecheckout/checkout_url')->getCheckoutUrl();
            Mage::app()->getResponse()
                ->setRedirect($url, 301)
                ->sendResponse();
        }
    }

    /**
     * helpful if the checkout was rewritten 
     * e.g. by mxperts noregion
     */
    public function setCheckoutMethods()
    {
        if (Mage::helper("onecheckout")->isActive()) {
            $this->_setCheckoutMethods("checkout.cart.methods");
            $this->_setCheckoutMethods("checkout.cart.top_methods");
        }
    }

    /**
     * set Checkout Methods Block
     * 
     * @param string $parentBlockName
     */
    protected function _setCheckoutMethods($parentBlockName)
    {
        $layout = Mage::app()->getLayout();

        $methodsBlock = $layout->getBlock($parentBlockName);

        if ($methodsBlock) {
            $methodBlock = $layout->createBlock(
                    'onecheckout/cart_method', 'checkout.cart.methods.onecheckout', array('template' => 'onecheckout/cart/method.phtml', 'before' => "-")
            );
            $methodsBlock->insert($methodBlock);
            $methodsBlock->unsetChild("checkout.cart.methods.onepage");
        }
    }

    /**
     * set Addresses
     */
    public function setAddresses($observer)
    {
        Mage::helper("onecheckout")->setAddresses();
    }
}
