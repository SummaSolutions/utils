<?php
/**
 * Cms Page Export Block
 *
 * @category Summa
 * @package  Summa_Cms
 * @author   "Pablo Cianci" <pcianci@summasolutions.net>
 *
 */
class Summa_Cms_Block_Adminhtml_Cms_Page_Export
    extends Summa_Cms_Block_Adminhtml_Cms_Export_Abstract
{
    /**
     * Returns the page's title.
     *
     * @return string
     *
     */
    public function getPageTitle()
    {
        return $this->helper('summa_cms')->__('Pages Export');
    }

    /**
     * Returns the entity type to export.
     *
     * @return string
     *
     */
    public function getEntityTypeToExport()
    {
        return Summa_Cms_Model_ExportImport_Abstract::ENTITY_PAGE;
    }

    /**
     * Returns the export url.
     *
     * @return string
     *
     */
    public function getExportUrl()
    {
        return $this->getUrl('*/cms/exportPagesPost',array('_current'=>true));
    }
}