<?php
/**
 * @author: Facundo Capua
 * Date: 5/3/13
 */

class Summa_AutoSuggest_Model_Engine_Cms
{

   protected $_adapter = null;

    /**
     * Initialize search engine adapter
     *
     * @return Enterprise_Search_Model_Resource_Engine
     */
    protected function _initAdapter()
    {
        $this->_adapter = $this->_getAdapterModel('solr');

        $this->_adapter->setAdvancedIndexFieldPrefix($this->getFieldsPrefix());
        if (!$this->_canAllowCommit()) {
            $this->_adapter->holdCommit();
        }

        return $this;
    }

    /**
     * Set search engine adapter
     */
    public function __construct()
    {
        $this->_initAdapter();
    }


    /**
     * Retrieve search engine adapter model by adapter name
     * Now supporting only Solr search engine adapter
     *
     * @param string $adapterName
     * @return object
     */
    protected function _getAdapterModel($adapterName)
    {
//        switch ($adapterName) {
//            case 'solr':
//            default:
//                if (extension_loaded('solr')) {
//                    $modelName = 'enterprise_search/adapter_phpExtension';
//                } else {
//                    $modelName = 'enterprise_search/adapter_httpStream';
//                }
//                break;
//        }

        $modelName = 'summa_autosuggest/adapter_cms';

        $adapter = Mage::getSingleton($modelName);

        return $adapter;
    }


    /**
     * Remove entity data from search index
     *
     * For deletion of all documents parameters should be null. Empty array will do nothing.
     *
     * @param  int|array|null $storeIds
     * @param  int|array|null $entityIds
     * @param  string $entityType 'product'|'cms'
     * @return Enterprise_Search_Model_Resource_Engine
     */
    public function cleanIndex($storeIds = null, $entityIds = null, $entityType = 'product')
    {
        if ($storeIds === array() || $entityIds === array()) {
            return $this;
        }
        $queries = array(
            'content_type:cms'
        );

        $this->_adapter->deleteDocs(array(), $queries);

        return $this;
    }


    /**
     * Add entities data to search index
     *
     * @param int $storeId
     * @param array $entityIndexes
     * @param string $entityType 'product'|'cms'
     *
     * @return Enterprise_Search_Model_Resource_Engine
     */
    public function saveEntityIndexes($storeId, $entityIndexes, $entityType = 'product')
    {
        $docs = $this->_adapter->prepareDocsPerStore($entityIndexes, $storeId);
        $this->_adapter->addDocs($docs);

        return $this;
    }

    /**
     * Returns advanced index fields prefix
     *
     * @deprecated after 1.11.2.0
     *
     * @return string
     */
    public function getFieldsPrefix()
    {
        return '';
    }

    /**
     * Check if allow commit action is possible depending on current commit mode
     *
     * @return bool
     */
    protected function _canAllowCommit()
    {
        $commitMode = Mage::getStoreConfig(
            Enterprise_Search_Model_Indexer_Indexer::SEARCH_ENGINE_INDEXATION_COMMIT_MODE_XML_PATH
        );

        return $commitMode == Enterprise_Search_Model_Indexer_Indexer::SEARCH_ENGINE_INDEXATION_COMMIT_MODE_FINAL
            || $commitMode == Enterprise_Search_Model_Indexer_Indexer::SEARCH_ENGINE_INDEXATION_COMMIT_MODE_PARTIAL;
    }

    /**
     * Retrieve found document ids search index sorted by relevance
     *
     * @param string $query
     * @param array  $params see description in appropriate search adapter
     * @param string $entityType 'product'|'cms'
     * @return array
     */
    public function getIdsByQuery($query, $params = array(), $entityType = 'product')
    {
        return $this->_adapter->getIdsByQuery($query, $params);
    }
}