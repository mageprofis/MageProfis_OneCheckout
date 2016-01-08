<?php
/**
 *
 * @category   MageProfis
 * @package    MageProfis_OneCheckout
 * @copyright  Copyright (c) 2015 Ulrich Abelmann
 * @copyright  Copyright (c) 2015 MageProfis GmbH
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MageProfis_OneCheckout_IndexController
extends Mage_Checkout_Controller_Action
{

    public function indexAction()
    {
        if (!Mage::helper('checkout')->canOnepageCheckout()) {
            Mage::getSingleton('checkout/session')->addError($this->__('The onepage checkout is disabled.'));
            $this->_redirect('checkout/cart');
            return;
        }
        $quote = $this->getQuote();
        if (!$quote->hasItems() || $quote->getHasError()) {
            $this->_redirect('checkout/cart');
            return;
        }
        if (!$quote->validateMinimumAmount()) {
            $error = Mage::getStoreConfig('sales/minimum_order/error_message');
            Mage::getSingleton('checkout/session')->addError($error);
            $this->_redirect('checkout/cart');
            return;
        }
        $this->initCheckout();
        $this->loadLayout();
        $this->_initLayoutMessages('customer/session');
        $this->getLayout()->getBlock('head')->setTitle($this->__('Checkout'));
        $this->renderLayout();
    }

    /**
     *
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
     * init Address
     *
     * @param array $address
     */
    protected function initAddress($address)
    {
        $country = $address->getCountryId();
        if (empty($country)) {
            $defaultCountry = Mage::getStoreConfig('general/country/default');
            $address
                    ->setCountryId($defaultCountry)
                    ->setCollectShippingRates(true);
        }
    }

    /**
     * init Checkout
     */
    protected function initCheckout()
    {
        Mage::getSingleton('checkout/session')->setCartWasUpdated(false);
        //Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_secure'=>true)));
        //$this->getCheckout()->initCheckout();


        $quote = $this->getQuote();
        $quote->setTotalsCollectedFlag(false);
        $helper = Mage::helper('onecheckout/methods');

        $address = $quote->getShippingAddress();
        $this->initAddress($address);
        $this->initAddress($quote->getBillingAddress());

        // shipping method
        $selectedShippingMethod = $address->getShippingMethod();
        if (empty($selectedShippingMethod)) {
            if ($firstShippingMethod = $helper->getFirstShipping()) {
                $this->getCheckout()->saveShippingMethod($firstShippingMethod);
            }
        }

        // payment method
        $selectedPaymentMethod = $quote->getPayment()->getMethod();
        if (empty($selectedPaymentMethod)) {
            if ($firstPaymentMethod = $helper->getFirstPayment()) {
                $data = array(
                    "method" => $firstPaymentMethod,
                );
                $this->getCheckout()->savePayment($data);
            }
        }

        $quote->collectTotals();
    }

    /**
     * check if multicheckout is active and
     * remove all other addresses from quote
     *
     * @return MageProfis_OneCheckout_IndexController
     */
    public function preDispatch()
    {
        parent::preDispatch();
        $this->_preDispatchValidateCustomer();

        $checkoutSessionQuote = Mage::getSingleton('checkout/session')->getQuote();
        if ($checkoutSessionQuote->getIsMultiShipping()) {
            $checkoutSessionQuote->setIsMultiShipping(false);
            $checkoutSessionQuote->removeAllAddresses();
        }
        return $this;
    }
}
