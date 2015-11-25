<?php
class Summa_Badge_Model_Resource_Badge_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('summa_badge/badge');
    }
}
