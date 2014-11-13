<?php
/**
 * Summa Cms Adminhtml Cms Controller Block
 *
 * @category Summa
 * @package  Summa_Cms
 * @author   "Pablo Cianci" <pcianci@summasolutions.net>
 *
 */
class Summa_Cms_Adminhtml_CmsController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Return the extension Cms Helper.
     *
     * @return Summa_Cms_Helper_Data
     *
     */
    private function getCmsHelper()
    {
        /**
         * @var $cmsHelper Summa_Cms_Helper_Data
         */
        $cmsHelper = Mage::helper('summa_cms');
        return $cmsHelper;
    }

    /**
     * Init Action.
     *
     * @return Summa_Cms_Adminhtml_CmsController
     *
     */
    protected function _initAction()
    {
        $cmsHelper = $this->getCmsHelper();

        // load layout, set active menu and breadcrumbs
        $this->loadLayout()
            ->_setActiveMenu('cms/page')
            ->_addBreadcrumb($cmsHelper->__('CMS'), $cmsHelper->__('CMS'))
            ->_addBreadcrumb($cmsHelper->__('Export/Import'), $cmsHelper->__('Export/Import'))
        ;
        return $this;
    }

    /**
     * Mass Csv Export entity action.
     *
     * @param $entity
     * @return Mage_Core_Controller_Varien_Action
     *
     */
    private function massCsvExportEntityAction($entity)
    {
        $cmsHelper = $this->getCmsHelper();

        $this->_initAction();
        $this->_title($cmsHelper->__('CMS'))->_title($cmsHelper->__('Pages Export'));
        $this->_addBreadcrumb($cmsHelper->__('Pages Export'),null);

        /**
         * @var $exportModel Summa_Cms_Model_Export
         */
        $exportModel = Mage::getModel('summa_cms/export');
        $pageIds = $this->getRequest()->getParam($entity.'_ids');
        $result = $exportModel->getEntityPreExportResume($entity, $pageIds);
        $block = $this->getLayout()->getBlock('summa_cms_'.$entity.'_export');
        if ($block) {
            $block->setResult($result);
        }

        return $this->renderLayout();
    }

    /**
     * Mass Csv Export Pages Action.
     * Will get an pre export resume, to check for dependencies.
     *
     * @return Mage_Core_Controller_Varien_Action
     *
     */
    public function massCsvExportPagesAction()
    {
        return $this->massCsvExportEntityAction(Summa_Cms_Model_ExportImport_Abstract::ENTITY_PAGE);
    }

    /**
     * Mass Csv Export Block Action.
     * Will get an pre export resume, to check for dependencies.
     *
     * @return Mage_Core_Controller_Varien_Action
     *
     */
    public function massCsvExportBlocksAction()
    {
        return $this->massCsvExportEntityAction(Summa_Cms_Model_ExportImport_Abstract::ENTITY_BLOCK);
    }

    /**
     * Full Export Pages Action
     * Will get an pre export resume, to check for dependencies.
     *
     * @return Mage_Core_Controller_Varien_Action
     *
     */
    public function fullCsvExportPagesAction()
    {
        $this->getRequest()->setParam('page_ids',null);
        return $this->massCsvExportPagesAction();
    }

    /**
     * Full Export Block Action.
     * Will get an pre export resume, to check for dependencies.
     *
     * @return Mage_Core_Controller_Varien_Action
     *
     */
    public function fullCsvExportBlocksAction()
    {
        $this->getRequest()->setParam('block_ids',null);
        return $this->massCsvExportBlocksAction();
    }

    /**
     * Full Export Hierarchy Action
     * Will get an pre export resume, to check for dependencies.
     *
     * @return Mage_Core_Controller_Varien_Action
     *
     */
    public function fullCsvExportHierarchyAction()
    {
        $this->getRequest()->setParam('hierarchy_ids',null);
        return $this->massCsvExportHierarchyAction();
    }

    /**
     * Export action.
     * Will show a list of possible entities to export, allowing to choose which ones will be exported.
     *
     * @return Mage_Core_Controller_Varien_Action
     *
     */
    public function exportAction()
    {
        $cmsHelper = $this->getCmsHelper();

        $this->_initAction();
        $this->_addBreadcrumb($cmsHelper->__('Export'), $cmsHelper->__('Export'));
        return $this->renderLayout();
    }

    /**
     * Export Post Action.
     * Fires the actual export process for the choosen entities.
     *
     * @return Mage_Adminhtml_Controller_Action
     *
     */
    public function exportPostAction()
    {
        $params = $this->getRequest()->getParam('export_entity');

        if (
                empty($params)
                ||
                (
                    array_key_exists(Summa_Cms_Model_ExportImport_Abstract::ENTITY_BLOCK, $params)
                    &&
                    array_key_exists(Summa_Cms_Model_ExportImport_Abstract::ENTITY_PAGE, $params)
                )
            )
        {
            /**
             * @var $exportModel Summa_Cms_Model_Export
             */
            $exportModel = Mage::getModel('summa_cms/export');

            /**
             * @var $adminhtmlHelper Mage_Adminhtml_Helper_Data
             */
            $adminhtmlHelper = Mage::helper('adminhtml');

            $postData = new Varien_Object($this->getRequest()->getPost());
            $result = $exportModel->generateExportFile($postData);

            if ($result->getExportWasOk()) {
                $result->setDownloadUrl($adminhtmlHelper->getUrl('*/cms/downloadFile',array('filename'=>$result->getFilename())));
            }

            if ($this->getRequest()->isAjax()) {
                Mage::app()->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                return $this;
            } else {
                $this->_initAction();
                $block = $this->getLayout()->getBlock('summa_cms_export_result');
                if ($block) {
                    $block->setResult($result);
                }
                return $this->renderLayout();
            }

        } elseif (array_key_exists(Summa_Cms_Model_ExportImport_Abstract::ENTITY_BLOCK, $params)) {
            if ($this->getRequest()->isAjax()) {
                $result = array(
                    'redirect_url' => $this->getUrl('*/cms/fullCsvExportBlocks', array('_current'=>true))
                );

                Mage::app()->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                return $this;
            } else {
                return $this->fullCsvExportBlocksAction();
            }
        } elseif (array_key_exists(Summa_Cms_Model_ExportImport_Abstract::ENTITY_PAGE, $params)) {
            if ($this->getRequest()->isAjax()) {
                $result = array(
                    'redirect_url' => $this->getUrl('*/cms/fullCsvExportPages', array('_current'=>true))
                );

                Mage::app()->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                return $this;
            } else {
                return $this->fullCsvExportPagesAction();
            }
        } elseif (array_key_exists(Summa_Cms_Model_ExportImport_Abstract::ENTITY_HIERARCHY, $params)) {
            if ($this->getRequest()->isAjax()) {
                $result = array(
                    'redirect_url' => $this->getUrl('*/cms/fullCsvExportHierarchy', array('_current'=>true))
                );

                Mage::app()->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                return $this;
            } else {
                return $this->fullCsvExportHierarchyAction();
            }
        }

    }

    /**
     * Export Block Post Action.
     * Will actually export the data, generating the files.
     *
     * @return Mage_Core_Controller_Varien_Action
     *
     */
    public function exportBlocksPostAction()
    {
        $this->getRequest()->setParam('export_entity',null);
        return $this->exportPostAction();
    }

    /**
     * Export Pages Post Action
     * Will actually export the data, generating the files.
     *
     * @return Mage_Core_Controller_Varien_Action
     *
     */
    public function exportPagesPostAction()
    {
        $this->getRequest()->setParam('export_entity',null);
        return $this->exportPostAction();
    }

    /**
     * Export Pages Post Action
     * Will actually export the data, generating the files.
     *
     * @return Mage_Core_Controller_Varien_Action
     *
     */
    public function exportHierarchyPostAction()
    {
        $this->getRequest()->setParam('export_entity',null);
        return $this->exportPostAction();
    }

    /**
     * Download File Action.
     * Returns the file.
     *
     * @return Mage_Core_Controller_Varien_Action
     *
     */
    public function downloadFileAction()
    {
        /**
         * @var $exportModel Summa_Cms_Model_Export
         */
        $exportModel = Mage::getModel('summa_cms/export');

        $filename = $this->getRequest()->getParam('filename');
        $content = $exportModel->getFileContent($filename);

        $this->_prepareDownloadResponse($filename, $content, Summa_Cms_Model_Export_File::MIME_TYPE);

    }

    /**
     * Import Action.
     * Will show a list of files to import and allows to upload a file too.
     *
     * @return Mage_Core_Controller_Varien_Action
     *
     */
    public function importAction()
    {
        $cmsHelper = $this->getCmsHelper();

        $this->_initAction();
        $this->_addBreadcrumb($cmsHelper->__('Import'), $cmsHelper->__('Import'));
        return $this->renderLayout();
    }

    /**
     * Process File Action.
     * Tries to import the file and show status message.
     *
     * @return Mage_Adminhtml_Controller_Action
     *
     */
    public function processFileAction()
    {
        $cmsHelper = $this->getCmsHelper();

        /**
         * @var $importModel Summa_Cms_Model_Import
         */
        $importModel = Mage::getModel('summa_cms/import');

        $filename = $this->getRequest()->getParam('import-file');

        try {
            $importModel->setAdminUserId(Mage::getSingleton('admin/session')->getUser()->getUserId());
            $importModel->import($filename);
            Mage::getSingleton('adminhtml/session')->addSuccess($cmsHelper->__('File imported successfully.'));
        }
        catch (Exception $e){
            Mage::getSingleton('adminhtml/session')->addError($cmsHelper->__('There was an error trying to import the file:').' '.$e->getMessage());
        }

        return $this->_redirect('*/cms/import');
    }

    /**
     * Upload File Action.
     * Uploads the file, and if it is specified, also processes it.
     *
     * @return Mage_Adminhtml_Controller_Action
     *
     */
    public function uploadFileAction()
    {
        /**
         * @var $cmsHelper Summa_Cms_Helper_Data
         */
        $cmsHelper = $this->getCmsHelper();

        /**
         * @var $importModel Summa_Cms_Model_Import
         */
        $importModel = Mage::getModel('summa_cms/import');

        $alsoImport = (bool) $this->getRequest()->getParam('also-import-checkbox',false);
        try {
            $filename = $importModel->uploadFile('upload_file');
            Mage::getSingleton('adminhtml/session')->addSuccess($cmsHelper->__('File uploaded successfully.'));
            if ($alsoImport) {
                $this->getRequest()->setParam('import-file',$filename);
                return $this->_forward('processFile');
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($cmsHelper->__('There was an error trying to upload the file:').' '.$e->getMessage());
        }
        return $this->_redirect('*/cms/import');
    }
}