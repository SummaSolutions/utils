<?php

/* @var $installer Mage_Core_Model_Resource_Setup */
$installer = $this;
$connection = $installer->getConnection();

$installer->startSetup();

$table = $installer->getTable('summa_andreani/branch');
$regionTable = $installer->getTable('directory/country_region');

$installer->getConnection()->addColumn($table,
    'region_id',
    array(
        'type' => Varien_Db_Ddl_Table::TYPE_SMALLINT,
        'nullable'  => false,
        'default' => 0,
        'comment' => 'Region ID'
    )
);

$installer->getConnection()
    ->addForeignKey('region_id', $table, 'region_id', $regionTable, 'region_id',
        Varien_Db_Ddl_Table::ACTION_CASCADE, Varien_Db_Ddl_Table::ACTION_CASCADE);

$installer->endSetup();