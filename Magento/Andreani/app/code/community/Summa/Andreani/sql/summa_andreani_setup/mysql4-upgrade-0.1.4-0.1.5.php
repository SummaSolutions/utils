<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        17/04/15
 * Time:        09:19
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */
/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

try {
    $installer->getConnection()
        ->addColumn($installer->getTable('sales/shipment'), 'summa_andreani_shipment_status', array(
            'type'    => Varien_Db_Ddl_Table::TYPE_INTEGER,
            'comment' => 'summa_andreani_shipment_status',
        ));
} catch(Exception $e) {}

$installer->endSetup();