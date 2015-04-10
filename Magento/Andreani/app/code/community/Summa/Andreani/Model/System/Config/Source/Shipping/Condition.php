<?php

class Summa_Andreani_Model_System_Config_Source_Shipping_Condition
{
    public function toOptionArray()
    {
        $arr = array();

        if (Mage::helper('summa_andreani')->getConfigDataFromMatrixRates('active')) {
            foreach (Mage::getSingleton('matrixrate_shipping/carrier_matrixrate')->getCode('condition_name') as $k=>$v) {
                $arr[] = array('value'=>$k, 'label'=>$v);
            }
        }

        return $arr;
    }
}
