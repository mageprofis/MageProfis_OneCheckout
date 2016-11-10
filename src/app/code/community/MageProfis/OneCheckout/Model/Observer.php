<?php
/**
 *
 * @category   MageProfis
 * @package    MageProfis_OneCheckout
 * @copyright  Copyright (c) 2015 Ulrich Abelmann
 * @copyright  Copyright (c) 2015 MageProfis GmbH
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MageProfis_OneCheckout_Model_Observer
extends Mage_Core_Model_Abstract
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
        } else {
            $this->isDefaultCheckout($observer);
        }
    }

    /**
     * helpful if the checkout was rewritten 
     * e.g. by mxperts noregion
     */
    public function setCheckoutMethods()
    {
        if (Mage::helper('onecheckout')->isActive()) {
            $this->_setCheckoutMethods('checkout.cart.methods');
            $this->_setCheckoutMethods('checkout.cart.top_methods');
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
        Mage::helper('onecheckout')->setAddresses();
    }

    /**
     * 
     * @param Varien_Event_Observer $observer
     */
    public function addCommentToOrder($observer)
    {
        if($this->getRequest()->isPost())
        {
            $comment = $this->getRequest()->getPost('ordercomment', false);
            if($comment && strlen(trim($comment)) > 0)
            {
                $order = $observer->getOrder();
                /* @var Mage_Sale_Model_Order $order */
                $comment = nl2br(Mage::helper('core')->escapeHtml(trim($comment)));
                $order->setCustomerComment($comment);
                $order->setCustomerNoteNotify(true);
                $order->setCustomerNote($comment);
            }
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

    /**
     * load alternative layout if Wbcomm-magento-boilerplate is used
     * @param type $event
     */
    public function addLayoutXml($event)
    {
        if ($this->isAdmin())
        {
            return;
        }
        if (Mage::getConfig()->getModuleConfig('Webcomm_MagentoBoilerplate')->is('active', 'true'))
        {
            $xml = $event->getUpdates()
                    ->addChild('onecheckout_boilerplate');
            $xml->addAttribute('module', 'MageProfis_OneCheckout');
            $xml->addChild('file', 'onecheckout-boilerplate.xml');
        }
        if (Mage::getStoreConfig('onecheckout/compatibility/payone'))
        {
            $xml = $event->getUpdates()
                    ->addChild('onecheckout_payone');
            $xml->addAttribute('module', 'MageProfis_OneCheckout');
            $xml->addChild('file', 'onecheckout/payone.xml');
        }
        if (Mage::getStoreConfig('onecheckout/compatibility/billpay'))
        {
            $xml = $event->getUpdates()
                    ->addChild('onecheckout_billpay');
            $xml->addAttribute('module', 'MageProfis_OneCheckout');
            $xml->addChild('file', 'onecheckout/billpay.xml');
        }
    }

    /**
     * 
     * @param Varien_Event_Observer $event
     */
    public function isDefaultCheckout($event)
    {
        Mage::getSingleton('checkout/session')->setIsOneStepCheckout(false);
    }

    /**
     * Check in wich area we are
     * 
     * @return boolean
     */
    protected function isAdmin()
    {
        if(Mage::app()->getStore()->isAdmin())
        {
            return true;
        }

        if(Mage::getDesign()->getArea() == 'adminhtml')
        {
            return true;
        }

        return false;
    }
}
