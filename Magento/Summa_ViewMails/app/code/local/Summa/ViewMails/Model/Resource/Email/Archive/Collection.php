<?php
/**
 *
 * @category   Summa
 * @package    Summa_ViewMails
 * @author     Francisco T. Lopez <flopez@summasolutions.net>
 */
class Summa_ViewMails_Model_Resource_Email_Archive_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    /**
     * Init collection
     *
     */
    public function _construct()
    {
        $this->_init('holdyourfire_viewmails/email_archive');
    }

    public function delete()
    {
        foreach ($this->getItems() as $k=>$item) {
            $item->delete();
            unset($this->_items[$k]);
        }
        return $this;
    }
}
