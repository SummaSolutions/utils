<?php
/**
 * @author: Facundo Capua
 * Date: 5/3/13
 */

class Summa_AutoSuggest_Model_Indexer_Cms extends Mage_Index_Model_Indexer_Abstract
{

    protected function _registerEvent(Mage_Index_Model_Event $event)
    {
        return $this;
    }

    protected function _processEvent(Mage_Index_Model_Event $event)
    {
        return $this;
    }

    /**
     * Retrieve Indexer name
     *
     * @return string
     */
    public function getName()
    {
        return Mage::helper('summa_autosuggest')->__('CMS Search Index');
    }

    /**
     * Retrieve Indexer description
     *
     * @return string
     */
    public function getDescription()
    {
        return Mage::helper('summa_autosuggest')->__('Rebuild CMS search index');
    }

    /**
     * Rebuild all index data
     *
     */
    public function reindexAll()
    {
        try {
            $this->_getIndexer()->rebuildIndex();
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Retrieve Fulltext Search instance
     *
     * @return Summa_AutoSuggest_Model_Cms
     */
    protected function _getIndexer()
    {
        return Mage::getSingleton('summa_autosuggest/cms');
    }
}