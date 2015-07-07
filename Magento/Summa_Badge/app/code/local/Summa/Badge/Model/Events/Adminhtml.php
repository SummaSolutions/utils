<?php

class Summa_Badge_Model_Events_Adminhtml
{

    /**
     * Remove badges attributes from general product tab
     */
    public function removeFromGeneralTab($event)
    {
        foreach ($event->getForm()->getElements() as $fieldset) {
            for ($i = 1; $i <= Mage::helper('summa_badge')->getNroBadges(); $i++) {
                $fieldset->removeField('badge_' . $i);
            }
        }
        $fieldset->removeField('badge_pdp');
    }

    /**
     * Remove badges attributes from general product tab in update
     * attributes action
     */
    public function removeFromUpdateAttributesGeneralTab($event)
    {
        $object = $event->getObject();
        $excluded = $object->getFormExcludedFieldList();

        for ($i = 1; $i <= Mage::helper('summa_badge')->getNroBadges(); $i++) {
            $excluded[] = 'badge_' . $i;
        }
        $excluded[] = 'badge_pdp';
        $object->setFormExcludedFieldList($excluded);
    }

    /**
     * Remove badges cache when flush the catalog images.
     */
    public function cleanCache()
    {
        $cache_dir = Mage::getBaseDir('media') . '/wysiwyg/badges/cache/';
        $io = new Varien_Io_File();
        try {
            $io->rmdir($cache_dir, true);
        } catch (Exception $e) {
            throw new Exception("Unable to remove cache dir: '{$cache_dir}'. Access forbidden.");
        }
    }

    public function addToOptionEvent($observer)
    {

    }
}