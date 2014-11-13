<?php
/**
 * Cms Export Abstract Block
 *
 * @category Summa
 * @package  Summa_Cms
 * @author   "Pablo Cianci" <pcianci@summasolutions.net>
 *
 */
abstract class Summa_Cms_Block_Adminhtml_Cms_Export_Abstract
    extends Mage_Adminhtml_Block_Abstract
{
    /**
     * Returns the result's pages to export identifiers.
     *
     * @return mixed
     *
     */
    public function getPagesToExportIdentifiers()
    {
        return $this->getResult()->getToExportIdentifiers(Summa_Cms_Model_ExportImport_Abstract::ENTITY_PAGE);
    }

    /**
     * Returns the result's blocks to export identifiers.
     *
     * @return mixed
     *
     */
    public function getBlocksToExportIdentifiers()
    {
        return $this->getResult()->getToExportIdentifiers(Summa_Cms_Model_ExportImport_Abstract::ENTITY_BLOCK);
    }

    /**
     * Returns the result's page hierarchy to export identifiers.
     *
     * @return mixed
     *
     */
    public function getHierarchyToExportIdentifiers()
    {
        return $this->getResult()->getToExportIdentifiers(Summa_Cms_Model_ExportImport_Abstract::ENTITY_HIERARCHY);
    }

    /**
     * Returns the result's to export collection.
     *
     * @return mixed
     *
     */
    public function getToExportCollection()
    {
        return $this->getResult()->getToExportCollection();
    }

    /**
     * Returns all the result's collected dependencies for a given entity id.
     *
     * @param null $entityId
     * @return array
     *
     */
    public function getAllDependencies($entityId = null)
    {
        $dependencies = $this->getResult()->getDependencies();

        if (!empty($entityId)) {
            if (isset($dependencies[$entityId])) {
                $dependencies = $dependencies[$entityId];
            } else {
                $dependencies = array();
            }
        }
        return $dependencies;
    }

    /**
     * Returns all the result's collected dependencies for a given entity type and entity id.
     *
     * @param $entityType
     * @param $entityId
     * @return array
     *
     */
    public function getDependenciesByType($entityType, $entityId)
    {
        $dependencies = $this->getAllDependencies($entityId);

        if (isset($dependencies[$entityType])) {
            $dependencies = $dependencies[$entityType];
        } else {
            $dependencies = array();
        }
        return $dependencies;
    }

    /**
     * Accessor for the block dependencies of a given block.
     *
     * @param null $blockId
     * @return array
     *
     */
    public function getBlockDependencies($blockId = null)
    {
        return $this->getDependenciesByType(Summa_Cms_Model_ExportImport_Abstract::ENTITY_BLOCK, $blockId);
    }

    /**
     * Accessor for the page dependencies fo a given page.
     *
     * @param null $pageId
     * @return array
     *
     */
    public function getPageDependencies($pageId = null)
    {
        return $this->getDependenciesByType(Summa_Cms_Model_ExportImport_Abstract::ENTITY_PAGE, $pageId);
    }

    /**
     * Returns the export url.
     *
     * @return string
     *
     */
    public function getExportUrl()
    {
        return $this->getUrl('*/cms/exportPost',array('_current'=>true));
    }

    /**
     * Returns the auto resolve dependencies by default configured value.
     *
     * @return mixed
     *
     */
    public function getAutoResolveDependenciesByDefault()
    {
        return $this->helper('summa_cms/data')->getAutoResolveDependenciesByDefault();
    }

    /**
     * Returns the default filename for the export file.
     *
     * @return string
     *
     */
    public function getDefaultFilename()
    {
        return $this->helper('summa_cms/data')->getDefaultFilename();
    }

    public function getIdentifierLabel($entityType, $entityOrString)
    {
        /**
         * @var $cmsHelper Summa_Cms_Helper_Data
         */
        $cmsHelper = $this->helper('summa_cms');
        $entityTypeLabel = $cmsHelper->getLabelForEntitySingular($entityType);

        if (is_string($entityOrString)) {
            $identifierLabel = $entityTypeLabel.' '.$cmsHelper->__('Identifier:').' '.$entityOrString;
        } else {
            $entity = $entityOrString;
            $identifier = $entity->getIdentifier();
            if (empty($identifier)) {
                $identifierLabel = $entityTypeLabel.' '.$cmsHelper->__('ID:').' '.$entity->getId();
            } else {
                $identifierLabel = $entityTypeLabel.' '.$cmsHelper->__('Identifier:').' '.$identifier;
            }
        }

        return $identifierLabel;
    }


    /**
     * Returns the page's title.
     *
     * @return string
     *
     */
    abstract public function getPageTitle();

    /**
     * Returns the entity type to export.
     *
     * @return string
     *
     */
    abstract public function getEntityTypeToExport();
}