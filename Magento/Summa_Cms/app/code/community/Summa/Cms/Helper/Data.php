<?php
/**
 * Summa Cms Data Helper
 *
 * @category Summa
 * @package  Summa_Cms
 * @author   "Pablo Cianci" <pcianci@summasolutions.net>
 *
 */
class Summa_Cms_Helper_Data
    extends Mage_Core_Helper_Abstract
{
    /**
     * Constant to access the destination folder configuration.
     */
    const CONFIGURATION_PATH_DESTINATION_FOLDER = 'cms/summa_export_import/destination_folder';

    /**
     * Constant to access the auto resolve dependencies default value.
     */
    const CONFIGURATION_PATH_AUTO_RESOLVE_DEPENDENCIES_BY_DEFAULT = 'cms/summa_export_import/auto_resolve_dependecies_by_default';

    /**
     * Constant to access the upload and import default value.
     */
    const CONFIGURATION_PATH_UPLOAD_AND_IMPORT_BY_DEFAULT = 'cms/summa_export_import/upload_and_import_by_default';

    /**
     * Get the entity identifiers to export.
     *
     * @param $entityType
     * @param $toExportInformation
     * @return array
     *
     */
    public function getEntityIdentifiersToExport($entityType, $toExportInformation)
    {
        $entityDependencies = array();
        $entityIdentifiers = $toExportInformation->getToExportIdentifiers($entityType);
        if (empty($entityIdentifiers)) {
            $exportAll = (bool) $toExportInformation->getExportEntity($entityType);
            if ($exportAll) {
                $entityIdentifiers = Summa_Cms_Model_Export::EXPORT_ALL;
            } else {
                $entityIdentifiers = array();
            }
        }
        if ($toExportInformation->getAutoResolveDependencies()) {
            $entityDependencies = $toExportInformation->getToExportDependencies($entityType);
            if (empty($entityDependencies)) {
                $entityDependencies = array();
            }
        }

        if (is_array($entityIdentifiers)) {
            $entityIdentifiers = array_merge($entityIdentifiers, $entityDependencies);
            $entityIdentifiers = array_unique($entityIdentifiers);
        }

        return $entityIdentifiers;
    }

    /**
     * Returns the configured destination folder.
     *
     * @return string
     *
     */
    public function getDestinationFolder()
    {
        $baseDir = Mage::getBaseDir();
        $destinationFolder = Mage::getStoreConfig(self::CONFIGURATION_PATH_DESTINATION_FOLDER);
        return $baseDir.DS.$destinationFolder;
    }

    /**
     * Returns the upload and import by default configuration.
     *
     * @return bool
     *
     */
    public function getUploadAndImportByDefault()
    {
        return Mage::getStoreConfigFlag(self::CONFIGURATION_PATH_UPLOAD_AND_IMPORT_BY_DEFAULT);
    }

    /**
     * Returns the auto resolve dependencies by default configuration.
     *
     * @return bool
     *
     */
    public function getAutoResolveDependenciesByDefault()
    {
        return Mage::getStoreConfigFlag(self::CONFIGURATION_PATH_UPLOAD_AND_IMPORT_BY_DEFAULT);
    }

    /**
     * Returns the default filename for the export file.
     *
     * @return string
     *
     */
    public function getDefaultFilename()
    {
        return 'export-'.date('Y-m-d-H-i-s').'.csv';
    }

    /**
     * Returns the proper entity label according to the entity type.
     *
     * @param $entityCode
     * @return string
     *
     */
    public function getLabelForEntitySingular($entityCode)
    {
        switch ($entityCode) {
            case Summa_Cms_Model_ExportImport_Abstract::ENTITY_BLOCK: return $this->__('Static Block');
            case Summa_Cms_Model_ExportImport_Abstract::ENTITY_PAGE:  return $this->__('CMS Page');
            case Summa_Cms_Model_ExportImport_Abstract::ENTITY_HIERARCHY:  return $this->__('Page Hierarchy');
            default: return ucfirst($entityCode);
        }
    }

    /**
     * Returns the proper entity label according to the entity type.
     *
     * @param $entityCode
     * @return string
     *
     */
    public function getLabelForEntityPlural($entityCode)
    {
        switch ($entityCode) {
            case Summa_Cms_Model_ExportImport_Abstract::ENTITY_BLOCK: return $this->__('Static Blocks');
            case Summa_Cms_Model_ExportImport_Abstract::ENTITY_PAGE:  return $this->__('CMS Pages');
            case Summa_Cms_Model_ExportImport_Abstract::ENTITY_HIERARCHY:  return $this->__('Pages Hierarchy');
            default: return ucfirst($entityCode);
        }
    }
}