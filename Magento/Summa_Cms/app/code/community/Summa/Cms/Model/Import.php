<?php
/**
 * Summa Cms Model Import Model
 *
 * @category Summa
 * @package  Summa_Cms
 * @author   "Pablo Cianci" <pcianci@summasolutions.net>
 *
 */
class Summa_Cms_Model_Import extends Summa_Cms_Model_ExportImport_Abstract
{
    private $_linesRead = 0;

    private $_accessLevel = Enterprise_Cms_Model_Page_Version::ACCESS_LEVEL_PUBLIC;
    private $_adminUserId = null;

    public function setAdminUserId($adminUserId)
    {
        $this->_adminUserId = $adminUserId;
        return $this;
    }

    public function getAdminUserId()
    {
        return $this->_adminUserId;
    }

    /**
     * Returns the files stored in the configured destination folder.
     * The file is ordered by name in a descending order (Z to A).
     *
     * @return array
     *
     */
    public function getFilesToImport()
    {
        /**
         * @var $importHelper Summa_Cms_Helper_Data
         */
        $importHelper = Mage::helper('summa_cms/data');
        $destinationFolder = $importHelper->getDestinationFolder();
        $files = glob($destinationFolder.DS.'*.csv');
        $filesToExport = array();
        foreach ($files as $file) {
            $parts = pathinfo($file);
            $filesToExport[$file] = $parts['basename'];
        }
        krsort($filesToExport);
        return $filesToExport;
    }

    /**
     * Reads a csv line and increments the lines read.
     *
     * @param $fileHandler
     * @return array
     *
     */
    protected function readCsvLine($fileHandler)
    {
        $this->_linesRead++;
        return fgetcsv($fileHandler);
    }

    /**
     * Processes the file and then returns the results.
     *
     * @param $filename
     * @return array
     *
     */
    public function import($filename)
    {
        $file = fopen($filename,'r');
        $result = true;
        $lastEntityType = false;
        $lastHeaders = array();
        if ($file) {

            $collectedHierarchyNodeData = array();

            while (($data = $this->readCsvLine($file))) {
                $rowCount = count($data);
                if ($rowCount == 1) {
                    $entityType = strtolower($data[0]);
                    if (in_array($entityType, $this->getSupportedEntityTypes())) {
                        $lastEntityType = $entityType;
                        $lastHeaders = $this->readCsvLine($file);

                        if (!$this->validateHeaders($entityType, $lastHeaders)) {
                            Mage::throwException('Wrong headers for '.$entityType.' entity type on line #'.$this->_linesRead.'.');
                            $result = false;
                        }

                    } else {
                        Mage::throwException('Unknown entity type ('.$entityType.') on line #'.$this->_linesRead.'.');
                        $lastEntityType = false;
                        $lastHeaders = array();
                        $result = false;
                    }
                } elseif ($rowCount > 1 && !empty($lastHeaders)) {

                    $entityData = array();
                    foreach ($lastHeaders as $index=>$header) {
                        if ($lastEntityType == self::ENTITY_HIERARCHY && $header == 'page_id') {

                            if (!empty($data[$index])) {
                                $parts = explode('$$$', $data[$index]);
                                $pageIdentifier = $parts[0];
                                if (isset($parts[1])) {
                                    $stores = $parts[1];
                                    $stores = explode(',',$stores);
                                    $lookupStore = current($stores);
                                } else {
                                    $lookupStore = null;
                                }
                                $collection = Mage::getModel('cms/page')->getCollection();
                                if (!empty($lookupStore)) {
                                    $collection->addStoreFilter($this->getStoreByCode($lookupStore));
                                }
                                $collection->addFieldToFilter('identifier',$pageIdentifier);
                                $page = $collection->load()->getFirstItem();

                                //$page = Mage::getModel('cms/page')->load();
                                if ($page->getId()) {
                                    $entityData[$header] = $page->getId();
                                } else {
                                    $entityData[$header] = $data[$index];
                                }
                            } else {
                                $entityData[$header] = $data[$index];
                            }
                        } else {
                            $entityData[$header] = $data[$index];
                        }
                    }
                    if ($lastEntityType == self::ENTITY_HIERARCHY) {
                        $collectedHierarchyNodeData[] = $entityData;
                    } else {
                        $this->importEntity($lastEntityType, $entityData);
                    }
                    $result = true;

                } else {
                    $lastEntityType = false;
                    $lastHeaders = array();
                };
            }

            if (!empty($collectedHierarchyNodeData)) {
                /**
                 * @var $hierarchyNodeModel Enterprise_Cms_Model_Hierarchy_Node
                 */
                try {
                    $hierarchyNodeModel = $this->getEntityModel(self::ENTITY_HIERARCHY);
                    $hierarchyNodeModel->collectTree($collectedHierarchyNodeData, array());
                } catch (Exception $e) {
                    Mage::throwException('An error occurred while trying to import pages hierarchy data.');
                    $result = false;
                }
            }
        }
        fclose($file);
        return $result;
    }

    /**
     * Validate the headers for a given entity type.
     *
     * @param $entityType
     * @param $headers
     * @return bool
     *
     */
    public function validateHeaders($entityType, $headers)
    {
        return in_array('identifier', $headers);
    }

    /**
     * Returns the proper entity model depending on the entity type.
     *
     * @param $entityType
     * @return bool|false|Mage_Core_Model_Abstract
     *
     */
    protected function getEntityModel($entityType)
    {
        switch ($entityType) {
            case Summa_Cms_Model_ExportImport_Abstract::ENTITY_PAGE:  return Mage::getModel('cms/page');
            case Summa_Cms_Model_ExportImport_Abstract::ENTITY_BLOCK: return Mage::getModel('cms/block');
            case Summa_Cms_Model_ExportImport_Abstract::ENTITY_HIERARCHY: return Mage::getModel('enterprise_cms/hierarchy_node');
            default: return false;
        }
    }

    /**
     * Imports an entity.
     * It replaces the existent one if exists.
     *
     * @param $entityType
     * @param $entityData
     *
     */
    public function importEntity($entityType, $entityData)
    {
        $entityModel = $this->getEntityModel($entityType);

        if (isset($entityData['stores'])) {
            //prepare stores
            $storeCodes = trim($entityData['stores']);
            $storeIds = array();

            if (!empty($storeCodes)) {
                $storeCodes = explode(',',$storeCodes);
                foreach ($storeCodes as $storeCode) {
                    if (empty($storeCode)) {
                        $storeCode = Mage_Core_Model_Store::DEFAULT_CODE;
                    }
                    $store = $this->getStoreByCode($storeCode);
                    if ($store) {
                        $storeIds[] = $store->getId();
                    } else {
                        Mage::throwException('Unknown store code ('.$storeCode.') on line #'.$this->_linesRead);
                    }
                }
            }
            if (empty($storeIds)) {
                $storeIds = array(0);
            }

            $entityData['stores'] = $storeIds;
        }

        $stores = $entityData['stores'];

        // discard published_revision_id, cause, even if it exist, we need to preserve the last one in the actual environment.
        unset($entityData['published_revision_id']);
        unset($entityData['stores']);

        /**
         * @var $write Varien_Db_Adapter_Interface
         */
        $write = Mage::getSingleton('core/resource')->getConnection('core_write');

        $write->insertOnDuplicate(
            Mage::getSingleton('core/resource')->getTableName('cms/'.$entityType),
            $entityData
        );

        $entityStores = array();
        foreach ($stores as $storeId) {
            $entityStores[] = array(
                'store_id' => $storeId,
                $entityType.'_id' => $entityData[$entityType.'_id']
            );
        }

        $write->insertOnDuplicate(
            Mage::getSingleton('core/resource')->getTableName('cms/'.$entityType.'_store'),
            $entityStores
        );

        /* */
        if ($entityType == self::ENTITY_PAGE && $entityData['under_version_control'])
        {
            // try to load existent one
            $entityModel->load($entityData[$entityType.'_id']);
            $this->saveNewRevision($entityModel,$entityData);
        }
        /* */
    }

    /**
     * Uploads a given file and returns its new path.
     *
     * @param $fieldName
     * @return string
     *
     */
    public function uploadFile($fieldName)
    {
        /**
         * @var $importHelper Summa_Cms_Helper_Data
         */
        $importHelper = Mage::helper('summa_cms/data');
        $destinationFolder = $importHelper->getDestinationFolder();
        $uploader = new Mage_Core_Model_File_Uploader($fieldName);
        $uploader->save($destinationFolder);
        $filename = $uploader->getUploadedFileName();
        return $destinationFolder.DS.$filename;
    }

    /**
     * Saves a new page revision.
     *
     * @param $entityModel Mage_Cms_Model_Page
     * @param $entityData array
     *
     */
    public function saveNewRevision($entityModel, $entityData)
    {
        $revisionId = $entityModel->getPublishedRevisionId();

        $version = Mage::getModel('enterprise_cms/page_version')->load($entityModel->getPageId());
        if (!$version->getId()) {
            $write = Mage::getSingleton('core/resource')->getConnection('core_write');

            $userId = $this->getAdminUserId();

            $write->insertOnDuplicate(
                Mage::getSingleton('core/resource')->getTableName('enterprise_cms/page_version'),
                array(
                    'version_id' =>  $entityModel->getPageId(),
                    'label' => $entityModel->getTitle(),
                    'access_level' => $this->_accessLevel,
                    'page_id' => $entityModel->getPageId(),
                    'user_id' => $userId,
                    'revisions_count' => 1,
                    'version_number' => 1,
                    'created_at' => date('Y-m-d H:i:s')
                )
            );
        }

        /**
         * @var $revision Enterprise_Cms_Model_Page_Revision
         */
        $revision = Mage::getModel('enterprise_cms/page_revision');
        $userId = $this->getAdminUserId();
        $accessLevel = array($this->_accessLevel);

        if ($revisionId) {
            $revision->loadWithRestrictions($accessLevel, $userId, $revisionId);
        } else {
            // loading empty revision
            $versionId = $entityModel->getPageId();
            $pageId = $entityModel->getPageId();

            // loading empty revision but with general data from page and version
            $revision->loadByVersionPageWithRestrictions($versionId, $pageId, $accessLevel, $userId);
            $revision->setUserId($userId);
        }

        $originalData = $revision->getData();
        $newData = array_merge($originalData, $entityData);

        $revision->setData($newData)
            ->setUserId($userId);

        $revision->save();
        $revision->publish();
    }
}