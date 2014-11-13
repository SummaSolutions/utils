<?php
/**
 * Cms Page Grid Block
 *
 * @category Summa
 * @package  Summa_Cms
 * @author   "Pablo Cianci" <pcianci@summasolutions.net>
 *
 */
class Summa_Cms_Block_Adminhtml_Cms_Page_Grid
    extends Mage_Adminhtml_Block_Cms_Page_Grid
{
    /**
     * Prepares the columns. Extended only to add the export type.
     *
     * @return Mage_Adminhtml_Block_Widget_Grid
     *
     */
    protected function _prepareColumns()
    {
        $this->addExportType('*/cms/fullCsvExportPages', $this->helper('summa_cms')->__('Full CSV Export'));
        return parent::_prepareColumns();
    }

    /**
     * Prepares the mass actions to be used.
     *
     * @return Mage_Adminhtml_Block_Widget_Grid|Summa_Cms_Block_Adminhtml_Cms_Block_Grid
     *
     */
    public function _prepareMassaction()
    {
        /**
         * @var $cmsHelper Summa_Cms_Helper_Data
         */
        $cmsHelper = $this->helper('summa_cms');

        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setFormFieldName('page_ids');

        $this->getMassactionBlock()->addItem('export', array(
             'label' => $cmsHelper->__('Export to CSV file'),
             'url'   => $this->getUrl('*/cms/massCsvExportPages')
        ));

        return $this;
    }
}