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

class Loewenstark_OneCheckout_Model_Source_Trigger extends Mage_Core_Model_Abstract {

    public function toOptionArray() {
        $options = array('-none-', 'Country', 'Postcode', 'State/Region', "Shipping Method", "Payment Method");
        $temp = array();

        foreach($options as $option)	{
            $temp[] = array(
				'label' => Mage::helper("onecheckout")->__($option), 
				'value' => str_replace(array("/", " "), "_", strtolower($option))
			);
        }

        return $temp;
    }
}