<?php

class Summa_Andreani_Model_System_Config_Source_Shipping_MatrixratesFilter
{
    const COUNTRY_VALUE = 1;
    const REGION_VALUE = 2;
    const CITY_VALUE = 3;
    const ZIP_VALUE = 4;

    public function toOptionArray()
    {
        $arr = array(
            array(
                'value' => self::COUNTRY_VALUE,
                'label' => Mage::helper('summa_andreani')->__('Country')
            ),
            array(
                'value' => self::REGION_VALUE,
                'label' => Mage::helper('summa_andreani')->__('Region')
            ),
            array(
                'value' => self::CITY_VALUE,
                'label' => Mage::helper('summa_andreani')->__('City')
            ),
            array(
                'value' => self::ZIP_VALUE,
                'label' => Mage::helper('summa_andreani')->__('Postal code')
            ),
        );

        return $arr;
    }
}
