<?php
/**
 * Summa Cms Model Export Model
 *
 * @category Summa
 * @package  Summa_Cms
 * @author   "Pablo Cianci" <pcianci@summasolutions.net>
 *
 */
class Summa_Cms_Model_Export extends Summa_Cms_Model_ExportImport_Abstract
{

    const EXPORT_ALL = 'summa_cms_export_all';

    /**
     * Will store the collected dependencies.
     *
     * @var array
     *
     */
    private $_collectedDependencies = array();

    /**
     * Will store the only the entities identifiers to export.
     * @var array
     */
    private $_entityIdsToExport = array();

    /**
     * Will store all the stores in array form array(store id => store code).
     *
     * @var null
     *
     */
    private $_stores = null;

    /**
     * Returns the proper entity collection depending on the entity type.
     *
     * @param $entityType
     * @return bool|object
     *
     */
    protected function getEntityCollection($entityType)
    {
        switch ($entityType) {
            case self::ENTITY_PAGE:  return Mage::getModel('cms/page')->getCollection();
            case self::ENTITY_BLOCK: return Mage::getModel('cms/block')->getCollection();
            case self::ENTITY_HIERARCHY: return Mage::getModel('enterprise_cms/hierarchy_node')->getResourceCollection()->joinMetaData();
            default: return false;
        }
    }

    /**
     * Gets the pre export resume information for a given entity type and some given entity ids.
     * If no entity ids are defined, then all the entities will be used.
     *
     * @param $entityType
     * @param array $entityIds
     * @return Varien_Object
     *
     */
    public function getEntityPreExportResume($entityType, $entityIds = array())
    {
        $entityCollection = $this->getEntityCollection($entityType);
        if (!empty($entityIds)) {
            $entityCollection->addFieldToFilter($entityType.'_id',$entityIds);
        }
        $entityCollection->load();
        foreach ($entityCollection as $entity) {
            $this->_entityIdsToExport[] = $entity->getIdentifier();
        }

        $entityIdentifiersToExport = array(
            Summa_Cms_Model_ExportImport_Abstract::ENTITY_BLOCK => array(),
            Summa_Cms_Model_ExportImport_Abstract::ENTITY_PAGE  => array(),
            Summa_Cms_Model_ExportImport_Abstract::ENTITY_HIERARCHY => array(),
        );
        foreach ($entityCollection as $entity) {
            $entityIdentifiersToExport[$entityType][] = $entity->getIdentifier();
            $this->checkDependencies($entityType, $entity);
        }

        return new Varien_Object(array(
            'to_export_collection'  => $entityCollection,
            'to_export_identifiers' => $entityIdentifiersToExport,
            'dependencies'          => $this->_collectedDependencies
        ));
    }

    /**
     * Checks the dependencies of the given entity,
     *
     * @param $entityType
     * @param $entity
     *
     */
    protected function checkDependencies($entityType, $entity)
    {
        if ($entityType == self::ENTITY_HIERARCHY) {

            $dependantPageId = $entity->getPageId();
            if (!empty($dependantPageId)) {
                $page = Mage::getModel('cms/page')->load($dependantPageId);
                $this->_collectedDependencies[$entity->getId()] = array(self::ENTITY_PAGE=>array($page->getId()=>$page->getIdentifier()));
            }

        } else {
            /**
             * @var $filter Summa_Cms_Model_Export_Filter
             */
            $filter = Mage::getModel('summa_cms/export_filter');
            $this->_collectedDependencies[$entity->getId()] = $filter->collectDependencies($entity);
        }
    }

    /**
     * Returns the id fiel name depending on the entity type.
     *
     * @param $entityType
     * @return string
     *
     */
    protected function getIdFieldNameByEntityType($entityType)
    {
        switch ($entityType) {
            case self::ENTITY_BLOCK: return 'block_id';
            case self::ENTITY_PAGE: return 'page_id';
            case self::ENTITY_HIERARCHY: return 'node_id';
        }
    }

    /**
     * Adds an entity type collection to the export file.
     *
     * @param $entityType string
     * @param $toExportInformation array
     * @param $exportFileModel Summa_Cms_Model_Export_File
     * @return Summa_Cms_Model_Export
     *
     */
    protected function addEntityCollectionToExportModel($entityType, $toExportInformation, $exportFileModel)
    {
        /**
         * @var $exportHelper Summa_Cms_Helper_Data
         */
        $exportHelper = Mage::helper('summa_cms/data');
        $entityIdentifiersToExport = $exportHelper->getEntityIdentifiersToExport($entityType, $toExportInformation);

        if (!empty($entityIdentifiersToExport)) {
            $entityCollectionToExport = $this->getEntityCollection($entityType);
            $entityCollectionToExport->addFieldToSelect('*');
            if ($entityIdentifiersToExport != Summa_Cms_Model_Export::EXPORT_ALL) {
                $entityCollectionToExport->addFieldToFilter('main_table.'.$this->getIdFieldNameByEntityType($entityType) ,$entityIdentifiersToExport);
            }
            $entityCollectionToExport->load();
            if ($entityType == Summa_Cms_Model_Export::ENTITY_HIERARCHY) {
                $this->changePageIdToPageIdentifiers($entityCollectionToExport);
            } else {
                $this->addStoresIdentifiersAndFilterContent($entityCollectionToExport);
            }
            $exportFileModel->addEntityCollectionToExportByEntityType($entityType, $entityCollectionToExport);
        }
        return $this;
    }

    /**
     * Collects the models to export, pre-process them and generates the export file.
     * Returns the error messages or generated filename.
     *
     * @param $toExportInformation
     * @return string
     *
     */
    public function generateExportFile($toExportInformation)
    {
        /**
         * @var $exportHelper Summa_Cms_Helper_Data
         */
        $exportHelper = Mage::helper('summa_cms/data');

        $filename = $toExportInformation['custom_file_name'];
        if (empty($filename)) {
            $filename = $exportHelper->getDefaultFilename();
        }

        /**
         * @var $exportFileModel Summa_Cms_Model_Export_File
         */
        $exportFileModel = Mage::getModel('summa_cms/export_file', array('export_model'=>$this));
        $exportFileModel->setFilename($filename)
                        ->setDestinationFolder($exportHelper->getDestinationFolder());

        foreach ($this->getSupportedEntityTypes() as $entityType) {
            $this->addEntityCollectionToExportModel($entityType, $toExportInformation, $exportFileModel);
        }

        try {
            $filename = $exportFileModel->generate();
            $response = array(
                'export_was_ok' => true,
                'filename'      => $filename,
            );
        } catch (Exception $e) {
            $response = array(
                'export_was_ok' => false,
                'error_message' => $exportHelper->__('There was an error trying to export the file:').' '.$e->getMessage()
            );
        }

        return new Varien_Object($response);
    }

    /**
     * Return all the stores configured in Magento
     * in array form array(store id => store code).
     *
     * @return array|null
     *
     */
    public function getAllStores()
    {
        if (is_null($this->_stores)) {
            $stores = array();
            $allStores = Mage::app()->getStores();
            foreach ($allStores as $store)
            {
                $stores[$store->getId()] = $store->getCode();
            }
            $this->_stores = $stores;
        }
        return $this->_stores;
    }

    /**
     * Replaces store ids by store codes and filters the content.
     *
     * @param $entityCollection
     *
     */
    public function addStoresIdentifiersAndFilterContent($entityCollection)
    {
        /**
         * @var $filter Summa_Cms_Model_Export_Filter
         */
        $filter = Mage::getModel('summa_cms/export_filter');
        $stores = $this->getAllStores();
        if (!empty($entityCollection)) {
            foreach ($entityCollection as $entity) {
                $entity->load($entity->getId());

                $filteredContent = $filter->filter($entity->getContent());
                $entity->setContent($filteredContent);

                $itemStores = array();
                foreach ($entity->getStoreId() as $storeId) {
                    if (isset($stores[$storeId])) {
                        $itemStores[] = $stores[$storeId];
                    } else {
                        $itemStores[] = '';
                    }
                }
                $entity->setStores(implode(',',$itemStores));
                $entity->unsetData('store_id');
            }
        }
    }

    /**
     * Replaces page ids with page identifiers.
     *
     * @param $entityCollection
     *
     */
    public function changePageIdToPageIdentifiers($entityCollection)
    {

        $stores = $this->getAllStores();

        foreach ($entityCollection as $entity) {
            $dependantPageId = $entity->getPageId();
            if (!empty($dependantPageId)) {
                $page = Mage::getModel('cms/page')->load($dependantPageId);
                if ($page->getId()) {
                    $itemStores = array();
                    foreach ($page->getStoreId() as $storeId) {
                        if (isset($stores[$storeId])) {
                            $itemStores[] = $stores[$storeId];
                        } else {
                            $itemStores[] = '';
                        }
                    }

                    $stores = implode(',',$itemStores);
                    $entity->setPageId($page->getIdentifier().'$$$'.$stores);
                }
            }
        }
    }

    /**
     * Returns the content of the specified filename,
     *
     * @param $filename
     * @return string
     *
     */
    public function getFileContent($filename)
    {
        /**
         * @var $exportHelper Summa_Cms_Helper_Data
         */
        $exportHelper = Mage::helper('summa_cms/data');
        $destinationFolder = $exportHelper->getDestinationFolder();
        $filename = $destinationFolder.DS.$filename;
        return file_get_contents($filename);
    }
}