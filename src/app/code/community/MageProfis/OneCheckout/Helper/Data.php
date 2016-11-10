<?php

/**
 *
 * @category   MageProfis
 * @package    MageProfis_OneCheckout
 * @copyright  Copyright (c) 2015 Ulrich Abelmann
 * @copyright  Copyright (c) 2015 MageProfis GmbH
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MageProfis_OneCheckout_Helper_Data
extends Mage_Core_Helper_Abstract
{
    /**
     * get Refresh Trigger
     * 
     * @param string $key
     * @return string
     */
    public function getTriggers($key)
    {
        $vals = explode(',', Mage::getStoreConfig('onecheckout/refresh/' . $key));
        if (in_array('-none-', $vals)) {
            $vals = array();
        }
        if (count($vals) == 0) {
            return 'new Array()';
        } else {
            return 'new Array("' . implode('","', $vals) . '")';
        }
    }

    /**
     * 
     * @return bool
     */
    public function isActive()
    {
        return Mage::getStoreConfigFlag('onecheckout/general/active');
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
     * set Address from Post
     * 
     * @return array
     */
    public function setAddresses()
    {
        if ($this->getCheckout()->getIsOneStepCheckout())
        {
            $result = array();
            if ($this->getRequest()->isPost()) {
                $data = $this->getRequest()->getPost('billing', array());
                $customerAddressId = $this->getRequest()->getPost('billing_address_id', false);
                if (isset($data['email'])) {
                    $data['email'] = trim($data['email']);
                }
                $method = $this->getRequest()->getPost('checkout_method', false);
                if ($method) {
                    $newMethod = Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER;
                    $this->getCheckout()->getQuote()->setCheckoutMethod($newMethod);
                }

                $result['savebilling'] = $this->getCheckout()->saveBilling($data, $customerAddressId);


                $usingCase = isset($data['use_for_shipping']) ? (int) $data['use_for_shipping'] : 0;
                if (!$usingCase) {
                    $data = $this->getRequest()->getPost('shipping', array());
                }
                $customerAddressId = $this->getRequest()->getPost('shipping_address_id', false);
                $result['saveshipping'] = $this->getCheckout()->saveShipping($data, $customerAddressId);
            }
            return $result;
        }
    }

    /**
     * 
     * @return Mage_Core_Controller_Request_Http
     */
    protected function getRequest()
    {
        return Mage::app()->getRequest();
    }
}
