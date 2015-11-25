<?php
class Summa_Badge_Model_Resource_Badge
    extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('summa_badge/badge', 'badge_id');
    }
}
