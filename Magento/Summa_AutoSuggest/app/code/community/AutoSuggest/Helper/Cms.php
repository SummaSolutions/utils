<?php
/**
 * @author: Facundo Capua
 * Date: 5/7/13
 */

class Summa_AutoSuggest_Helper_Cms extends Mage_Core_Helper_Abstract
{
    /**
     * @return Summa_AutoSuggest_Model_Engine
     */
    public function getEngine()
    {
        return Mage::getSingleton('summa_autosuggest/engine_cms');
    }
}