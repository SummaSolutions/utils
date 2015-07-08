<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        17/04/15
 * Time:        08:44
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */
class Summa_Andreani_Model_System_Config_Source_Shipping_ProductAttributes
{

    public function toOptionArray()
    {
        $arr = array(
            array(
                'value' => '',
                'label' => Mage::helper('summa_andreani')->__('Select one product attribute')
            )
        );

        $attributes = Mage::getResourceModel('catalog/product_attribute_collection')->addVisibleFilter()->load();
        /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        foreach($attributes as $attribute) {
            $arr[] = array(
                'value' => $attribute->getAttributeCode(),
                'label' => $attribute->getFrontendLabel()
            );
        }

        return $arr;
    }
}