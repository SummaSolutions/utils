<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        28/04/15
 * Time:        17:01
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */

class Summa_Andreani_Model_System_Config_Source_Shipping_MinimumSettings
{
    const SET_MINIMUM_LIMIT = 0;
    const SET_CUSTOM = 1;
    const SET_EXCEPTION = 2;

    public function toOptionArray()
    {
        $arr = array(
            array(
                'value' => self::SET_MINIMUM_LIMIT,
                'label' => Mage::helper('summa_andreani')->__('Set to minimal limit')
            ),
            array(
                'value' => self::SET_CUSTOM,
                'label' => Mage::helper('summa_andreani')->__('Set to custom value')
            ),
            array(
                'value' => self::SET_EXCEPTION,
                'label' => Mage::helper('summa_andreani')->__('Raise exception')
            )
        );

        return $arr;
    }
}