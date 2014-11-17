<?php
/**
 * @author: Facundo Capua
 * Date: 5/3/13
 */

class Summa_AutoSuggest_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected static $_productCollection = null;

    protected static $_rootCategory = null;

    /**
     * Retrieve products collection based on search query
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getProductCollection()
    {
        if(self::$_productCollection === null){
            if ($this->isSolrEnabled()) {
                $query = Mage::getSingleton('enterprise_search/search_layer');

                $collection = $query->getProductCollection();
                $categoryIds = $this->_getCategoryIds();

                $collection->setFacetCondition('category_ids', $categoryIds);
            } else {
                $query = Mage::helper('catalogsearch')->getQuery();
                /* @var $query Mage_CatalogSearch_Model_Query */
                $query->setStoreId(Mage::app()->getStore()->getId());
                $collection = $query->getResultCollection();
            }

            $collection->addAttributeToSelect('name');
            $collection->addAttributeToSelect('thumbnail');

            self::$_productCollection = $collection;
        }

        return self::$_productCollection;
    }

    /**
     * Retrieve category collection based on search query
     *
     * @return Varien_Data_Collection
     */
    public function getCategoryCollection()
    {
        $productCollection = $this->getProductCollection();
        $categoryFacet = $productCollection->getFacetedData('category_ids');
        $categoryFacet = array_filter($categoryFacet, function($value){
            return !empty($value);
        });

        if(!empty($categoryFacet)){
            $categoryIds = array_keys($categoryFacet);
            $categoryCollection = Mage::getModel('catalog/category')->getCollection()
                                    ->addAttributeToSelect('name');
            $categoryCollection
                ->addFieldToFilter('entity_id', array('in' => $categoryIds))
                ->addFieldToFilter('level', array('gteq' => $this->_getConfig()->getMinimumCategoryLevel()));
            $categoryCollection->load();
            $categoriesArray = $categoryCollection->toArray();

            usort($categoriesArray, function ($a, $b) use($categoryFacet) {
                $productCountA = $categoryFacet[$a['entity_id']];
                $productCountB = $categoryFacet[$b['entity_id']];

                if ($productCountA == $productCountB) {
                    return $a['level'] < $b['level'] ? -1 : 1;
                }

                return $productCountA > $productCountB ? -1 : 1;
            });
            $limit                = $this->_getConfig()->getCategoriesCollectionLimit();
            $categoriesFinalArray = array_slice($categoriesArray, 0, $limit);
            $collection           = new Varien_Data_Collection;
            foreach ($categoriesFinalArray as $categoryArray) {
                $category = $categoryCollection->getItemById($categoryArray['entity_id']);
                $productCount = $categoryFacet[$category->getId()];

                if ($productCount) {
                    $category->setData('product_count', $productCount);
                    $collection->addItem($category);
                }
            }

            return $collection;
        }

    }


    /**
     * Retrieve cms pages collection based on search query
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    public function getCmsPagesCollection()
    {
        /* @var $collection Summa_AutoSuggest_Model_Resource_Page_Collection */
        $collection = Mage::getModel('cms/page')->getCollection();
        $query = Mage::helper('catalogsearch')->getQuery();

        if (Mage::helper('enterprise_search')->getIsEngineAvailableForNavigation(false)) {
            $collection->setSearchTerm($query->getQueryText());
        }

        $limit      = $this->_getConfig()->getCmsPagesCollectionLimit();
        $collection->setPageSize($limit);

        return $collection;
    }

    /**
     * @return Summa_AutoSuggest_Helper_Config
     */
    protected function _getConfig()
    {
        return Mage::helper('summa_autosuggest/config');
    }

    /**
     * @return Mage_Catalog_Model_Category
     */
    protected function _getRootCategory()
    {
        if(self::$_rootCategory === null){
            $categoryId = Mage::app()->getStore()->getRootCategoryId();
            $category = Mage::getModel('catalog/category')->load($categoryId);

            self::$_rootCategory = $category;
        }


        return self::$_rootCategory;
    }

    /*
     *
     */
    protected function _getCategoryIds()
    {
        $categories = Mage::helper('catalog/category')->getStoreCategories(false, true);
        $return = $categories->getAllIds();

        return $return;
    }

    /**
     * @return bool
     */
    public function isSolrEnabled()
    {
        return (bool) Mage::helper('enterprise_search')->getIsEngineAvailableForNavigation(false);
    }


    /**
     * @param $category
     * @return string
     */
    public function getCategoryUrl($category)
    {
        if($this->_getConfig()->getCategoryLinkType() == Summa_AutoSuggest_Model_Source_Category_Link_Type::CATEGORY_PAGE){
            return $category->getUrl();
        }else{
            $query = Mage::helper('catalogsearch')->getQuery();

            return $this->_getUrl('catalogsearch/result', array('q' => $query->getQueryText(),'cat' => $category->getId()));
        }
    }
}