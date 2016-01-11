<?php

$installer = $this;
$installer->startSetup();

$configuration = array(
    'label'        => 'Badge PDP',
    'user_defined' => '1',
    'required'     => false,
    'type'         => 'int',
    'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'input'        => 'select',
    'source'       => 'eav/entity_attribute_source_table'
);
$entityTypeId = $installer->getEntityTypeId('catalog_product');
$installer->addAttribute($entityTypeId, 'badge_pdp', $configuration);

$allAttributeSetIds = $installer->getAllAttributeSetIds('catalog_product');

foreach ($allAttributeSetIds as $attributeSetId) {
    $installer->addAttributeToSet('catalog_product', $attributeSetId, 'general', 'badge_pdp');
}


$installer->endSetup();