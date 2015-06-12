<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        17/04/15
 * Time:        09:19
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */

/** @var $catalogSetup Mage_Catalog_Model_Resource_Eav_Mysql4_Setup */
$catalogSetup = Mage::getResourceModel('catalog/setup', 'core_setup');
$installer = $this;
$installer->startSetup();

/* Things you will need */
$entityTypeId = $catalogSetup->getEntityTypeId('catalog_product');

/* Get the ID of the Default attribute set */
$attributeSetDefaultId = $catalogSetup->getDefaultAttributeSetId($entityTypeId);

/*Create attributes*/
// We suggest use weight default attribute of magento but if you need use another here you have an example to create a new one
$catalogSetup->addAttribute($entityTypeId, 'weight_andreani', array(
    'label' => 'Weight for Andreani (gr)',
    'input' => 'text',
    'type' => 'int',
    'default_value' => '',
    'is_filterable' => '1',
    'is_filterable_in_search' => '1',
    'visible' => '0',
    'visible_on_front' => '1',
    'is_global'  => '1',
    'required' => '0',
    'is_searchable' => '1',
    'is_comparable' => '1',
    'user_defined' => '1',
    'sort_order' => '60'
));

$catalogSetup->addAttribute($entityTypeId, 'height_andreani', array(
    'label' => 'Height for Andreani (cm)',
    'input' => 'text',
    'type' => 'int',
    'default_value' => '',
    'is_filterable' => '1',
    'is_filterable_in_search' => '1',
    'visible' => '1',
    'visible_on_front' => '1',
    'is_global'  => '1',
    'required' => '1',
    'is_searchable' => '1',
    'is_comparable' => '1',
    'user_defined' => '1',
    'sort_order' => '60',
    'frontend_class' => 'required-entry validate-number validate-number-range number-range-1-90'
));
// We suggest use width default attribute of magento but if you need use another here you have an example to create a new one
$catalogSetup->addAttribute($entityTypeId, 'width_andreani', array(
    'label' => 'Width for Andreani (cm)',
    'input' => 'text',
    'type' => 'int',
    'default_value' => '',
    'is_filterable' => '1',
    'is_filterable_in_search' => '1',
    'visible' => '1',
    'visible_on_front' => '1',
    'is_global'  => '1',
    'required' => '1',
    'is_searchable' => '1',
    'is_comparable' => '1',
    'user_defined' => '1',
    'sort_order' => '70',
    'frontend_class' => 'required-entry validate-number validate-number-range number-range-1-90'
));
// We suggest use length default attribute of magento but if you need use another here you have an example to create a new one
$catalogSetup->addAttribute($entityTypeId, 'length_andreani', array(
    'label' => 'Length for Andreani (cm)',
    'input' => 'text',
    'type' => 'int',
    'default_value' => '',
    'is_filterable' => '1',
    'is_filterable_in_search' => '1',
    'visible' => '1',
    'visible_on_front' => '1',
    'is_global'  => '1',
    'required' => '1',
    'is_searchable' => '1',
    'is_comparable' => '1',
    'user_defined' => '1',
    'sort_order' => '80',
    'frontend_class' => 'required-entry validate-number validate-number-range number-range-1-90'
));

/*Get attributes set*/
$attributesSet = Mage::getModel('eav/entity_attribute_set')->getCollection();

foreach ($attributesSet as $attributeSet) {
    $attributeSetId = $attributeSet->getAttributeSetId();

    $catalogSetup->addAttributeToSet($entityTypeId, $attributeSetId, 'General', 'height_andreani', '60');
    $catalogSetup->addAttributeToSet($entityTypeId, $attributeSetId, 'General', 'width_andreani', '70');
    $catalogSetup->addAttributeToSet($entityTypeId, $attributeSetId, 'General', 'length_andreani', '80');
    $catalogSetup->addAttributeToSet($entityTypeId, $attributeSetId, 'General', 'weight_andreani', '90');
}

$installer->endSetup();