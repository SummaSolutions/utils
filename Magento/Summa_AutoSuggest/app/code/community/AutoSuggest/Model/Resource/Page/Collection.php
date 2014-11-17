<?php
/**
 * @author: Facundo Capua
 * Date: 5/8/13
 */

class Summa_AutoSuggest_Model_Resource_Page_Collection extends Mage_Cms_Model_Resource_Page_Collection
{
    protected $_engine = null;

    protected $_searchedEntityIds = null;

    protected $_searchQueryFilters = null;

    protected $_searchQueryText = null;

    protected $_searchableFields = array(
        'cms_title',
        'cms_url_key',
        'cms_content_heading',
        'cms_content',
    );

    public function __construct()
    {
        if(Mage::helper('enterprise_search')->getIsEngineAvailableForNavigation(false)){
            $this->_engine = Mage::helper('summa_autosuggest/cms')->getEngine();
        }

        parent::__construct();
    }

    protected function _beforeLoad()
    {
        $ids = array();
        if($this->_searchQueryFilters){
            if ($this->_engine) {
                list($query, $params) = $this->_prepareBaseParams();

                if ($this->_pageSize !== false) {
                    $page              = ($this->_curPage  > 0) ? (int) $this->_curPage  : 1;
                    $rowCount          = ($this->_pageSize > 0) ? (int) $this->_pageSize : 1;
                    $params['offset']  = $rowCount * ($page - 1);
                    $params['limit']   = $rowCount;
                }

                $needToLoadFacetedData = (!$this->_facetedDataIsLoaded && !empty($this->_facetedConditions));
                if ($needToLoadFacetedData) {
                    $params['solr_params']['facet'] = 'on';
                    $params['facet'] = $this->_facetedConditions;
                }

                $result = $this->_engine->getIdsByQuery($query, $params);
                $ids    = (array) $result['ids'];

                if ($needToLoadFacetedData) {
                    $this->_facetedData = $result['faceted_data'];
                    $this->_facetedDataIsLoaded = true;
                }
            }

            $this->_searchedEntityIds = &$ids;
            $this->getSelect()->where('main_table.page_id IN (?)', $this->_searchedEntityIds);

            /**
             * To prevent limitations to the collection, because of new data logic.
             * On load collection will be limited by _pageSize and appropriate offset,
             * but third party search engine retrieves already limited ids set
             */
            $this->_storedPageSize = $this->_pageSize;
            $this->_pageSize = false;
        }

        return parent::_beforeLoad();
    }

    /**
     * Prepare base parameters for search adapters
     *
     * @return array
     */
    protected function _prepareBaseParams()
    {
        $store  = Mage::app()->getStore();
        $params = array(
            'store_id'      => $store->getId(),
            'locale_code'   => $store->getConfig(Mage_Core_Model_Locale::XML_PATH_DEFAULT_LOCALE),
            'filters'       => $this->_searchQueryFilters
        );
        $params['filters']     = $this->_searchQueryFilters;

        if (!empty($this->_searchQueryParams)) {
            $params['ignore_handler'] = true;
            $query = $this->_searchQueryParams;
        } else {
            $query = $this->_searchQueryText;
        }

        return array($query, $params);
    }

    public function setSearchTerm($term)
    {
        $this->_searchQueryText = $term;
//        foreach($this->_searchableFields as $field){
//            $this->addSearchQfFilter($field, $term);
//        }
        $this->addSearchQfFilter('content_type', 'cms-page');
    }

    public function addSearchQfFilter($param, $value = null)
    {
        if (is_array($param)) {
            foreach ($param as $field => $value) {
                $this->addSearchQfFilter($field, $value);
            }
        } elseif (isset($value)) {
            if (isset($this->_searchQueryFilters[$param]) && !is_array($this->_searchQueryFilters[$param])) {
                $this->_searchQueryFilters[$param] = array($this->_searchQueryFilters[$param]);
                $this->_searchQueryFilters[$param][] = $value;
            } else {
                $this->_searchQueryFilters[$param] = $value;
            }
        }

        return $this;
    }
}