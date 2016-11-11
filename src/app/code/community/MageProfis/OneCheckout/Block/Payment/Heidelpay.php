<?php
/**
 *
 * @category   MageProfis
 * @package    MageProfis_OneCheckout
 * @copyright  Copyright (c) 2015 Ulrich Abelmann
 * @copyright  Copyright (c) 2015 MageProfis GmbH
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MageProfis_OneCheckout_Block_Payment_Heidelpay
extends Mage_Core_Block_Abstract
{
    protected function _toHtml()
    {
        if (Mage::getConfig()->getModuleConfig('HeidelpayCD_Edition')->is('active', 'true'))
        {
            return '<script type="text/javascript" src="'.$this->getSkinUrl('onecheckout/payment/heidelpay.js').'"></script>';
        }
        return '';
    }
}