<?php

/**
 *
 * @category   MageProfis
 * @package    MageProfis_OneCheckout
 * @copyright  Copyright (c) 2015 Ulrich Abelmann
 * @copyright  Copyright (c) 2015 MageProfis GmbH
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MageProfis_OneCheckout_Block_Adminhtml_Website extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = '';

        $nameprefix = $element->getName();
        $idprefix = $element->getId();
        $element->setName($nameprefix . '[]');
        $value = array(0, 1);
        $c = Mage::getStoreConfig("onecheckout/general/content");

        if ($c != "" && $c != "false") {
            foreach (Mage::app()->getWebsites() as $w) {

                $element->setChecked(false);

                $id = $w->getId();
                $name = $w->getName();

                $element->setId($idprefix . '_' . $id);
                $element->setValue($id);
                $element->setClass('onecheckoutprosites');

                if ($p = Mage::helper("onecheckout")->c($w, Mage::helper("onecheckout")->l())) {
                    if (in_array($w->getId(), $p)) {
                        $element->setChecked(true);
                    }
                }

                if ($id != 0) {
                    $html .= '<div><label>' . $element->getElementHtml() . ' ' . $name . ' </label></div>';
                }
            }

            $max = 10;
            $html .= '
				<input id="' . $idprefix . '_diasbled" type="hidden" disabled="disabled" name="' . $nameprefix . '" />
				<script type="text/javascript">
				function updateOCPWebsites(){
					$("' . $idprefix . '_diasbled").disabled = "disabled";
					if($$(".onecheckoutprosites:checked").length >= ' . $max . '){
						$$(".onecheckoutprosites").each(function(e){
							if(!e.checked){
								e.disabled = "disabled";
							}
						});
					} else {
						$$(".onecheckoutprosites").each(function(e){
							if(!e.checked){
								e.disabled = "";
							}
						});
						if($$(".onecheckoutprosites:checked").length == 0){
							$("' . $idprefix . '_diasbled").disabled = "";
						}
					}
				}
				$$(".onecheckoutprosites").each(function(e){
					e.observe("click", function(){
						updateOCPWebsites();
					});
				});
				updateOCPWebsites();
			</script>';
        } else {
            $html = sprintf('<strong class="required">%s</strong>', $this->__('Please enter a PRO License Serial'));
        }

        return $html;
    }

}
