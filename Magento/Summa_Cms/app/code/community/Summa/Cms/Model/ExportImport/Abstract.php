<?php
/**
 * Summa Cms Model Export Import Abstract Model
 *
 * @category Summa
 * @package  Summa_Cms
 * @author   "Pablo Cianci" <pcianci@summasolutions.net>
 *
 */
class Summa_Cms_Model_ExportImport_Abstract
    extends Varien_Object
{
    /**
     * Constant for page entities.
     */
    const ENTITY_PAGE = 'page';

    /**
     * Constant for block entities.
     */
    const ENTITY_BLOCK = 'block';

    /**
     * Constant for hierarchy entities.
     */
    const ENTITY_HIERARCHY = 'node';

    /**
     * Returns the supported entities to import.
     *
     * @return array
     *
     */
    public function getSupportedEntityTypes()
    {
        return array(
            self::ENTITY_BLOCK,
            self::ENTITY_PAGE,
            self::ENTITY_HIERARCHY
        );
    }

    /**
     * Returns the store by code or false if not found.
     *
     * @param $storeCode
     * @return bool
     *
     */
    protected function getStoreByCode($storeCode)
    {
        if (is_null($this->_stores)) {
            $this->_stores = Mage::app()->getStores();
        }
        foreach($this->_stores as $store){
            if($store->getCode()==$storeCode) {
                return $store;
            }
        }
        return false;
    }
}