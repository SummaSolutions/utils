<?php

class Summa_Andreani_Model_System_Config_Source_Shipping_Service
{
    const ANDREANI_WEBSERVICE_VALUE = 1;
    const MATRIX_RATES_VALUE = 2;

    public function toOptionArray()
    {
        $arr = array(
            array(
                'value' => self::ANDREANI_WEBSERVICE_VALUE,
                'label' => Mage::helper('summa_andreani')->__('Andreani WebService')
            )
        );

        if (Mage::helper('summa_andreani')->getConfigDataFromMatrixRates('usable_for_andreani')) {
            $arr[] = array(
                'value' => self::MATRIX_RATES_VALUE,
                'label' => Mage::helper('summa_andreani')->__('Matrix Rates')
            );
        }

        return $arr;
    }
}
