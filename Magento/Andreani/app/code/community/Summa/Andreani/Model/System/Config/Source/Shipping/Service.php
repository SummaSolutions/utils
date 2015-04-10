<?php

class Summa_Andreani_Model_System_Config_Source_Shipping_Service
{
    const ANDREANI_WEBSERVICE_VALUE = 1;
    const MATRIX_RATES_VALUE = 1;

    public function toOptionArray()
    {
        $arr = array(
            array(
                'value' => self::ANDREANI_WEBSERVICE_VALUE,
                'label' => Mage::helper('summa_andreani')->__('Andreani WebService')
            ),
            array(
                'value' => self::MATRIX_RATES_VALUE,
                'label' => Mage::helper('summa_andreani')->__('Matrix Rates')
            ),
        );

        return $arr;
    }
}
