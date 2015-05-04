<?php

$installer = $this;
$installer->startSetup();


$table = $installer->getConnection()->newTable($installer->getTable('cms_tree'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
        'unsigned' => true,
        'nullable' => false,
        'primary' => true,
        'identity' => true,
    ), 'Id')
    ->addColumn('page_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 6, array(
        'unsigned' => false,
        'nullable' => true,
    ), 'Cms Page Id')
    ->addColumn('store_id', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5, array(
        'unsigned' => true,
        'nullable' => false,
    ), 'Store Id')
    ->addColumn('parent_id', Varien_Db_Ddl_Table::TYPE_INTEGER, 11, array(
        'nullable' => true,
        'default' => 0,
    ), 'Parent Cms Tree Id')
    ->addColumn('path', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => true,
        'default' => null,
    ), 'Cms Page Path')
    ->addColumn('position', Varien_Db_Ddl_Table::TYPE_SMALLINT, 5, array(
        'nullable' => true,
        'default' => null,
    ), 'Page Position Relative to Parent')
    ->addColumn('title', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => false,
        'default' => 0,
    ), 'Title')
    ->addColumn('level', Varien_Db_Ddl_Table::TYPE_SMALLINT,5, array(
        'nullable'=>false,
        'deafult'=>0
    ), 'Level')
    ->setComment('Cms Pages table');
$installer->getConnection()->createTable($table);

$installer->endSetup();