<?php

$installer = $this;

$installer->startSetup();

$table = $installer->getConnection()
    ->newTable($installer->getTable('summa_andreani/storepickup'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_SMALLINT, null, array(
        'identity'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'ID')
    ->addColumn('descripcion', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Descripcion')
    ->addColumn('sucursal_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Sucursal ID')
    ->addColumn('direccion', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Direccion')
    ->addColumn('horario', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Horario')
    ->addColumn('latitud', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Latitud')
    ->addColumn('longitud', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Longitud')
    ->addColumn('email', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Email')
    ->addColumn('tipo_telefono_1', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Tipo Telefono 1')
    ->addColumn('telefono_1', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => false,
    ), 'Telefono 1')
    ->addColumn('tipo_telefono_2', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => true,
    ), 'Tipo Telefono 2')
    ->addColumn('telefono_2', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => true,
    ), 'Telefono 2')
    ->addColumn('tipo_telefono_3', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => true,
    ), 'Tipo Telefono 3')
    ->addColumn('telefono_3', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable'  => true,
    ), 'Telefono 3')
    ->addColumn('codigo_postal', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => false,
    ), 'Codigo Postal')
    ->addColumn('localidad', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => false,
    ), 'Localidad')
    ->addColumn('provincia', Varien_Db_Ddl_Table::TYPE_VARCHAR, 255, array(
        'nullable' => false,
    ), 'Provincia')
    ->addColumn('enabled', Varien_Db_Ddl_Table::TYPE_BOOLEAN, 255, array(
        'nullable' => false,
    ), 'Enabled')
;

$installer->getConnection()->createTable($table);

$installer->endSetup();