<?php
/**
 *
 * @category   Summa
 * @package    Summa_ViewMails
 * @author     Francisco T. Lopez <flopez@summasolutions.net>
 */
class Summa_ViewMails_Model_Resource_Email_Archive extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Init resource model
     *
     */
    protected function _construct()
    {
        $this->_init('holdyourfire_viewmails/email_archive', 'archive_id');
    }
}
