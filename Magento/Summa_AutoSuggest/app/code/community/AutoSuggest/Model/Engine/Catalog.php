<?php

/**
 * @author: Facundo Capua
 * Date: 5/3/13
 */
class Summa_AutoSuggest_Model_Engine_Catalog
    extends Enterprise_Search_Model_Resource_Engine
{
    /**
     * Remove entity data from search index
     *
     * For deletion of all documents parameters should be null. Empty array will do nothing.
     *
     * @param  int|array|null $storeIds
     * @param  int|array|null $entityIds
     * @param  string         $entityType 'product'|'cms'
     *
     * @return Enterprise_Search_Model_Resource_Engine
     */
    public function cleanIndex($storeIds = null, $entityIds = null, $entityType = 'product')
    {
        if ($storeIds === array() || $entityIds === array()) {
            return $this;
        }

        if (is_null($storeIds) || $storeIds == Mage_Core_Model_App::ADMIN_STORE_ID) {
            $storeIds = array_keys(Mage::app()->getStores());
        } else {
            $storeIds = (array)$storeIds;
        }

        $queries = array();
        if (empty($entityIds)) {
            foreach ($storeIds as $storeId) {
                $queries[] = '(store_id:' . $storeId . ') AND (content_type:product)';
            }
        } else {
            $entityIds = (array)$entityIds;
            $uniqueKey = $this->_adapter->getUniqueKey();
            foreach ($storeIds as $storeId) {
                foreach ($entityIds as $entityId) {
                    $queries[] = $uniqueKey . ':' . $entityId . '|' . $storeId;
                }
            }
        }

        $this->_adapter->deleteDocs(array(), $queries);

        return $this;
    }
}