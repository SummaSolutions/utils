<?php

class Summa_Andreani_Model_Resource_Sucursal_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('summa_andreani/sucursal');
    }
}