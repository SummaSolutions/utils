<?php
/**
 *
 * @category   Summa
 * @package    Summa_ViewMails
 * @author     Francisco T. Lopez <flopez@summasolutions.net>
 */
class Summa_ViewMails_Model_Cron extends Mage_Core_Model_Abstract
{
    public function clearEmailArchive()
    {
        /* @var $helper Summa_ViewMails_Helper_Data */
        $helper = Mage::helper('holdyourfire_viewmails');

        $period = $helper->getPeriod();

        if ($helper->isEnabled() && $helper->canClearEmailArchive() && $period > 0) {
            $date = time() - $period * 3600 * 24;
            $date = date('Y-m-d H:i:s', $date);
            /* @var $collection Summa_ViewMails_Model_Resource_Email_Archive_Collection */
            $collection = Mage::getModel('holdyourfire_viewmails/email_archive')->getCollection();
            $collection->addFieldToFilter('created_at', array('lt' => $date));
            try {
                $collection->delete();
            } catch (Exception $e) {
                Mage::log('Error on Clear Email Archive');
            }
        }
    }
}
