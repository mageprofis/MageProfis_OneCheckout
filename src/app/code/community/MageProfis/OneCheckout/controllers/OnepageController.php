<?php 
/**
 *
 * @category   MageProfis
 * @package    MageProfis_OneCheckout
 * @copyright  Copyright (c) 2015 Ulrich Abelmann
 * @copyright  Copyright (c) 2015 MageProfis GmbH
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class MageProfis_OneCheckout_OnepageController extends Mage_Core_Controller_Front_Action {
	
    function indexAction() {
        $this->_redirect('onecheckout', array('_secure'=>true));
    }	
	
}
