<?php
/**
 *
 * @category   MageProfis
 * @package    MageProfis_OneCheckout
 * @copyright  Copyright (c) 2015 Ulrich Abelmann
 * @copyright  Copyright (c) 2015 MageProfis GmbH
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MageProfis_OneCheckout_Model_Source_Trigger
extends Mage_Core_Model_Abstract
{
    /**
     * 
     * @return array
     */
    public function toOptionArray()
    {
        $options = array('-none-', 'Country', 'Postcode', 'State/Region', 'Shipping Method', 'Payment Method');
        $temp = array();

        foreach ($options as $option) {
            $temp[] = array(
                'label' => Mage::helper('onecheckout')->__($option),
                'value' => str_replace(array("/", ' '), '_', strtolower($option))
            );
        }

        return $temp;
    }
}
