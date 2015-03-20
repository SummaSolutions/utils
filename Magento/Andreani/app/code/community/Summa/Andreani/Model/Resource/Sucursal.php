<?php

class Summa_Andreani_Model_Resource_Sucursal
    extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('summa_andreani/sucursal', 'id');
    }

}