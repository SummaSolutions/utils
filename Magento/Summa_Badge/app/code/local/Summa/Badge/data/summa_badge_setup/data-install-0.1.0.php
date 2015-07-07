<?php

$installer = $this;
$setup = new Mage_Eav_Model_Entity_Setup('core_setup');
$installer->startSetup();

for($i=1; $i<=Mage::helper('summa_badge')->getNroBadges(); $i++) {
	$configuration = array(
		'label'        => 'Badge '.$i,
		'user_defined' => '1',
		'required'     => false,
		'type'         => 'int',
		'global'       => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
		'input'        => 'select',
		'source'       => 'eav/entity_attribute_source_table'
	);

	$setup->addAttribute('catalog_product', 'badge_'.$i, $configuration);

    $allAttributeSetIds = $installer->getAllAttributeSetIds('catalog_product');

    foreach ($allAttributeSetIds as $attributeSetId) {
        $installer->addAttributeToSet('catalog_product', $attributeSetId, 'general', 'badge_'.$i);
    }
}

$installer->endSetup();