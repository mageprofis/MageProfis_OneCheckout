<?php
/**
 *
 * @category   MageProfis
 * @package    MageProfis_OneCheckout
 * @copyright  Copyright (c) 2015 Ulrich Abelmann
 * @copyright  Copyright (c) 2015 MageProfis GmbH
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MageProfis_OneCheckout_AjaxController
extends Mage_Core_Controller_Front_Action
{

    /**
     * @return Mage_Checkout_Model_Type_Onepage
     */
    protected function getOnepage()
    {
        return Mage::getSingleton('checkout/type_onepage');
    }

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
        return $this->getOnepage()->getQuote();
    }

    /**
     * 
     * @return MageProfis_OneCheckout_AjaxController
     */
    protected function _ajaxRedirectResponse()
    {
        $this->getResponse()
                ->setHeader('HTTP/1.1', '403 Session Expired')
                ->setHeader('Login-Required', 'true')
                ->sendResponse();
        return $this;
    }

    /**
     * 
     * @return boolean
     */
    protected function _expireAjax()
    {
        $quote = $this->getQuote();
        if (!$quote->hasItems() || $quote->getHasError() || $quote->getIsMultiShipping()) {
            $this->_ajaxRedirectResponse();
            return true;
        }
        $action = $this->getRequest()->getActionName();
        if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true)) {
            $this->_ajaxRedirectResponse();
            return true;
        }

        return false;
    }

    /**
     * set Redirect Url for Login Page
     * 
     * @return MageProfis_OneCheckout_AjaxController
     */
    public function preLoginAction()
    {
        Mage::getSingleton('customer/session')->setBeforeAuthUrl(Mage::getUrl('onecheckout'));
        return $this;
    }

    /**
     * set unCache for Ajax Requests
     * 
     * @return MageProfis_OneCheckout_AjaxController
     */
    protected function unCache()
    {
        $layout = $this->getLayout();
        $layout->getUpdate()->setCacheId('NOCACHE_' . microtime() . rand(1, 1000));
        return $this;
    }

    /**
     * get Shipping Methods Html
     * 
     * @return string
     */
    protected function _getShippingMethodsHtml()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $this->unCache();
        $update->load('checkout_onepage_shippingmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

    /**
     * get Payment Methods Html
     * 
     * @return string
     */
    protected function _getPaymentMethodsHtml()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $this->unCache();
        $update->load('checkout_onepage_paymentmethod');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

    /**
     * get Review Html
     * 
     * @return string
     */
    protected function _getReviewHtml()
    {
        $layout = $this->getLayout();
        $update = $layout->getUpdate();
        $this->unCache();
        $update->load('onecheckout_ajax_review');
        $layout->generateXml();
        $layout->generateBlocks();
        $output = $layout->getOutput();
        return $output;
    }

    /**
     * _merge Arrays
     * 
     * @param array $result
     * @param array $tmpResult
     * @return array
     */
    protected function _merge($result, $tmpResult)
    {
        if (is_array($tmpResult)) {
            $result = array_merge_recursive($result, $tmpResult);
        }
        return $result;
    }

    /**
     * update Action, returns json string
     * 
     * @return MageProfis_OneCheckout_AjaxController
     */
    public function updateAction()
    {
        if ($this->_expireAjax()) {
            return $this;
        }
        
        if (!$this->getRequest()->isPost()) {
            return $this;
        }

        $this->importDataToAddresses();
        $result = array();
        $result = $this->_merge($result, Mage::helper('onecheckout')->setAddresses());
        $result = $this->_merge($result, $this->saveShippingMethod());
        $result = $this->_merge($result, $this->savePayment());

        $this->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();

        $result['updates']['checkout-payment-method-load'] = $this->_getPaymentMethodsHtml();
        $result['updates']['checkout-shipping-method-load'] = $this->_getShippingMethodsHtml();
        $result['updates']['checkout-review-load'] = $this->_getReviewHtml();

        $this->getResponse()->setHeader('Content-type', 'application/json', true);
        $this->getResponse()->setHeader('X-Robots-Tag', 'noindex, nofollow', true);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        return $this;
    }

    /**
     * import Data To Adresses
     * 
     * @return MageProfis_OneCheckout_AjaxController
     */
    protected function importDataToAddresses()
    {
        $billingAddressId = $this->getRequest()->getPost('billing_address_id', false);
        $billingData = $this->getRequest()->getPost('billing', array());

        if (!$billingAddressId && !empty($billingData)) {
            $address = $this->getQuote()->getBillingAddress();
            $addressForm = Mage::getModel('customer/form');
            $addressForm->setFormCode('customer_address_edit')
                    ->setEntityType('customer_address')
                    ->setIsAjaxRequest(true);
            $addressForm->setEntity($address);
            $addressData = $addressForm->extractData($addressForm->prepareRequest($billingData));
            $addressForm->compactData($addressData);
        }

        $usingCase = isset($billingData['use_for_shipping']) ? (int) $billingData['use_for_shipping'] : 0;
        if ($usingCase) {
            $shippingData = $billingData;
        } else {
            $shippingData = $this->getRequest()->getPost('shipping', array());
        }
        $shippingAddressId = $this->getRequest()->getPost('shipping_address_id', false);
        if (!$shippingAddressId && !empty($shippingData)) {
            $address = $this->getQuote()->getShippingAddress();
            $addressForm = Mage::getModel('customer/form');
            $addressForm->setFormCode('customer_address_edit')
                    ->setEntityType('customer_address')
                    ->setIsAjaxRequest(true);
            $addressForm->setEntity($address);
            $addressData = $addressForm->extractData($addressForm->prepareRequest($shippingData));
            $addressForm->compactData($addressData);
        }
        return $this;
    }

    /**
     * save Shipping Method
     * 
     * @return Model
     */
    protected function saveShippingMethod()
    {
        if ($this->_expireAjax()) {
            return;
        }

        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPost('shipping_method', '');
            $result = $this->getCheckout()->saveShippingMethod($data);
            /*
              $result will have erro data if shipping method is empty
             */
            if (!$result) {
                Mage::dispatchEvent('checkout_controller_onepage_save_shipping_method', array('request' => $this->getRequest(),
                    'quote' => $this->getQuote()));
                $this->getQuote()->collectTotals();
            }
            return $result;
        }
    }

    /**
     * save Payment Method
     * 
     * @return type
     */
    protected function savePayment()
    {
        if ($this->_expireAjax()) {
            return;
        }
        try {
            if (!$this->getRequest()->isPost()) {
                $this->_ajaxRedirectResponse();
                return;
            }
            $data = $this->getRequest()->getPost('payment', array());

            // set payment to quote
            $result = array();
            //$result = $this->getCheckout()->savePayment($data);
            // do not use checkout function
            // see reason below in this function
            $result = $this->_savePayment($data);

            // get section and redirect data
            $redirectUrl = $this->getQuote()->getPayment()->getCheckoutRedirectUrl();
            if (empty($result['error']) && !$redirectUrl) {
                $this->loadLayout('checkout_onepage_review');
                $result['goto_section'] = 'review';
            }
            if ($redirectUrl) {
                $result['redirect'] = $redirectUrl;
            }
        } catch (Mage_Payment_Exception $e) {
            if ($e->getFields()) {
                $result['fields'] = $e->getFields();
            }
            $result['error'] = $e->getMessage();
        } catch (Mage_Core_Exception $e) {
            $result['error'] = $e->getMessage();
        } catch (Exception $e) {
            Mage::logException($e);
            $result['error'] = $this->__('Unable to set Payment Method.');
        }
        return $result;
    }

    /**
     * this method is modified from onepage checkout 
     * because the onepage checkout validates payment 
     * data once it is saved. we can not do that because 
     * after it is selected (e.g. cc) the details form is not 
     * filled yet.
     * 
     * @retrun array
     */
    protected function _savePayment($data)
    {
        if (empty($data)) {
            return array('error' => -1, 'message' => $this->__('Invalid data.'));
        }
        $quote = $this->getQuote();
        if ($quote->isVirtual()) {
            $quote->getBillingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        } else {
            $quote->getShippingAddress()->setPaymentMethod(isset($data['method']) ? $data['method'] : null);
        }

        // shipping totals may be affected by payment method
        if (!$quote->isVirtual() && $quote->getShippingAddress()) {
            $quote->getShippingAddress()->setCollectShippingRates(true);
        }

        //$payment = $quote->getPayment();
        //$payment->importData($data);
        $this->_paymentImportData($data);

        $quote->save();
        return array();
    }

    /**
     * taken from payment->importData
     */
    protected function _paymentImportData($data)
    {
        $payment = $this->getQuote()->getPayment();
        $data = new Varien_Object($data);

        // magento does not re-instanciate 
        // payment method if it was already set
        // and changed 
        if ($data->getMethod() != $payment->getMethod()) {
            $payment->unsMethodInstance();
        }

        $payment->setMethod($data->getMethod());
        $method = $payment->getMethodInstance();

        $this->getQuote()->collectTotals();

        if (!$method->isAvailable($this->getQuote())) {
            Mage::throwException(Mage::helper('sales')->__('The requested Payment Method is not available.'));
        }

        $method->assignData($data);
    }

}
