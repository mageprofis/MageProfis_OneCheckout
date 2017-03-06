<?php

/**
 *
 * @category   MageProfis
 * @package    MageProfis_OneCheckout
 * @copyright  Copyright (c) 2015 Ulrich Abelmann
 * @copyright  Copyright (c) 2015 MageProfis GmbH
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MageProfis_OneCheckout_Helper_Agreements extends Mage_Core_Helper_Abstract
{

    protected function getKeywords()
    {
        $list = array();
        foreach (explode("\n", Mage::getStoreConfig("onecheckout/agreements/keywords")) as $word) {
            $word = trim($word);
            if ($word != "") {
                $list[] = Mage::helper('core')->escapeHtml($word, null);
            }
        }
        return $list;
    }

    public function getLinkedText($agreement, $template = false)
    {
        if(!$template) $template = "<a href=\"#\" onclick=\"\$('agreement-content-{{argument_id}}').toggle(); return false;\">{{word}}</a>";
        
        $text = Mage::helper('core')->escapeHtml($agreement->getCheckboxText(), null);

        foreach ($this->getKeywords() as $word) {
            $tmp_template = $template;
            $tmp_template = str_replace('{{word}}', $word, $tmp_template);
            $tmp_template = str_replace('{{argument_id}}', $agreement->getId(), $tmp_template);
            $text = str_replace($word, $tmp_template, $text);
        }
        return $text;
    }
    
    /**
     * if webcomm boilerplate is used, we can use bootstrap modals
     * @param type $agreement
     * @return type
     */
    public function getBoilerplateLinkedText($agreement)
    {
        $text = Mage::helper('core')->escapeHtml($agreement->getCheckboxText(), null);

        foreach ($this->getKeywords() as $word) {
            $text = str_replace($word, '<a href="#" data-toggle="modal" data-target="#agreement-content-' . $agreement->getId() . '" >' . $word . '</a>', $text);
        }
        return $text;
    }

}
