<?php
/**
 * @author: Facundo Capua
 * Date: 5/3/13
 */

class Summa_AutoSuggest_Model_Cms extends Mage_Core_Model_Abstract
{
    /**
     * Store search engine instance
     *
     * @var object
     */
    protected $_engine                   = null;


    /**
     * Init resource model
     *
     */
    protected function _construct()
    {
        $this->_engine = Mage::helper('summa_autosuggest/cms')->getEngine();
    }

    public function rebuildIndex($storeId = null, $productIds = null)
    {
        if (is_null($storeId)) {
            $storeIds = array_keys(Mage::app()->getStores());
            foreach ($storeIds as $storeId) {
                $this->_rebuildStoreIndex($storeId);
            }
        } else {
            $this->_rebuildStoreIndex($storeId);
        }

        return $this;
    }

    protected function _rebuildStoreIndex($storeId)
    {
        $this->cleanIndex($storeId);

        /**
         * TODO: Avoid indexing system cms pages: no-route, home, etc.
         */
        $cmsPagesCollection = Mage::getModel('cms/page')->getCollection();
        $cmsPagesCollection->addStoreFilter($storeId);
        $cmsPages = array();
        foreach($cmsPagesCollection as $cmsPage){
            $cmsPages[] = array(
                'id' => 'CMS'.$cmsPage->getId(),
                'unique' => 'CMS'.$cmsPage->getId(),
                'cms_page_id' => $cmsPage->getId(),
                'cms_title' => $cmsPage->getTitle(),
                'cms_url_key' => $cmsPage->getIdentifier(),
                'cms_content_heading' => $cmsPage->getContentHeading(),
                'cms_content' => $cmsPage->getContent(),
                'store_id' => $storeId,
                'in_stock' => false,
                'visibility' => 0
            );
        }

        if(!empty($cmsPages)){
            $this->_savePagesIndexes($storeId, $cmsPages);
        }

        $this->resetSearchResults();

        return $this;
    }

    /**
     * Delete search index data for store
     *
     * @param int $storeId Store View Id
     * @param int $productId Product Entity Id
     * @return Mage_CatalogSearch_Model_Resource_Fulltext
     */
    public function cleanIndex($storeId = null)
    {
        if ($this->_engine) {
            $this->_engine->cleanIndex($storeId);
        }

        return $this;
    }

    public function resetSearchResults()
    {

    }

    /**
     * Save CMS Pages indexes
     *
     * @param int $storeId
     * @param array $productIndexes
     * @return Mage_CatalogSearch_Model_Resource_Fulltext
     */
    protected function _savePagesIndexes($storeId, $cmsPages)
    {
        if ($this->_engine) {
            $this->_engine->saveEntityIndexes($storeId, $cmsPages);
        }

        return $this;
    }
}