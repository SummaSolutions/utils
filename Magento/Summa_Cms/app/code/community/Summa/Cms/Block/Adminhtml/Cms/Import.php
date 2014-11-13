<?php
/**
 * Summa Cms Import Block
 *
 * @category Summa
 * @package  Summa_Cms
 * @author   "Pablo Cianci" <pcianci@summasolutions.net>
 *
 */
class Summa_Cms_Block_Adminhtml_Cms_Import
    extends Mage_Adminhtml_Block_Abstract
{

    /**
     * Returns the title for the page.
     *
     * @return string
     *
     */
    public function getTitle()
    {
        return $this->helper('summa_cms')->__('CMS Import');
    }

    /**
     * Returns an array containing the files to import.
     *
     * @return array
     *
     */
    public function getFilesToImport()
    {
        /**
         * @var $importModel Summa_Cms_Model_Import
         */
        $importModel = Mage::getModel('summa_cms/import');
        $files = $importModel->getFilesToImport();
        return $files;
    }

    /**
     * Returns the url for the import post action.
     *
     * @return string
     *
     */
    public function getProcessFileUrl()
    {
        return $this->getUrl('*/cms/processFile', array('_current'=>true));
    }

    /**
     * Returns the url for the upload file action.
     *
     * @return string
     *
     */
    public function getUploadFileUrl()
    {
        return $this->getUrl('*/cms/uploadFile', array('_current'=>true));
    }

    /**
     * Returns the upload and import by default configured value.
     *
     * @return mixed
     *
     */
    public function getUploadAndImportByDefault()
    {
        return $this->helper('summa_cms/data')->getUploadAndImportByDefault();
    }
}