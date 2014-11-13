<?php
/**
 * Summa Cms Export Block
 *
 * @category Summa
 * @package  Summa_Cms
 * @author   "Pablo Cianci" <pcianci@summasolutions.net>
 *
 */
class Summa_Cms_Block_Adminhtml_Cms_Export
    extends Mage_Adminhtml_Block_Abstract
{

    /**
     * Returns the title for the page.
     *
     * @return string
     *
     */
    public function getPageTitle()
    {
        return $this->helper('summa_cms')->__('CMS Export');
    }

    /**
     * Returns an array containing the supported entity types.
     *
     * @return array
     *
     */
    public function getEntitiesToExport()
    {
        /**
         * @var $importModel Summa_Cms_Model_Export
         */
        $importModel = Mage::getModel('summa_cms/export');

        return $importModel->getSupportedEntityTypes();
    }

    /**
     * Returns the url for the import post action.
     *
     * @return string
     *
     */
    public function getExportPostUrl()
    {
        return $this->getUrl('*/cms/exportPost', array('_current'=>true));
    }
}