<?php
/**
 * @author: Facundo Capua
 * Date: 5/20/13
 */

class Summa_AutoSuggest_Block_Result_Cms extends Mage_Core_Block_Template
{
    protected $_pagesCollection = null;

    public function getCollection()
    {
        if($this->_pagesCollection === null){
            $collection = $this->_getHelper()->getCmsPagesCollection();
            $collection->load();

            $this->_pagesCollection = $collection;
        }

        return $this->_pagesCollection;
    }

    /**
     * @return Summa_AutoSuggest_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('summa_autosuggest');
    }

    public function isCmsSearchEnabled(){
        return Mage::getStoreConfig('search_results/cms/show');
    }
}