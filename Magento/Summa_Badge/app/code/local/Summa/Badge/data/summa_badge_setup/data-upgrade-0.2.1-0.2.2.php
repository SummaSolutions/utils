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
    'source'       => 'summa_badge/attribute_source_type'
);
$entityTypeId = $installer->getEntityTypeId('catalog_product');
$installer->addAttribute($entityTypeId, 'badge_pdp', $configuration);

$allAttributeSetIds = $installer->getAllAttributeSetIds('catalog_product');

foreach ($allAttributeSetIds as $attributeSetId) {
    $installer->addAttributeToSet('catalog_product', $attributeSetId, 'general', 'badge_pdp');
}


/******************************************************************************************/

for($i=1; $i<=Mage::helper('summa_badge')->getNumberBadges(); $i++) {
    $configuration = array(
        'label'        => 'Badge '.$i,
        'user_defined' => '1',
        'required'     => false,
        'type'         => 'int',
        'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'input'        => 'select',
        'source'       => 'summa_badge/attribute_source_type'
    );

    $installer->addAttribute('catalog_product', 'badge_'.$i, $configuration);

    $allAttributeSetIds = $installer->getAllAttributeSetIds('catalog_product');

    foreach ($allAttributeSetIds as $attributeSetId) {
        $installer->addAttributeToSet('catalog_product', $attributeSetId, 'general', 'badge_'.$i);
    }
}


$installer->endSetup();