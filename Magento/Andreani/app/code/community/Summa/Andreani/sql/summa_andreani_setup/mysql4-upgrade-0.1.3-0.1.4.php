<?php
/**
 * Created for  elburgues.
 * @author:     mhidalgo@summasolutions.net
 * Date:        18/02/15
 * Time:        11:58
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */

$installer = $this;
/* @var $salesSetup Mage_Sales_Model_Resource_Setup */
$salesSetup = Mage::getResourceModel('sales/setup', 'core_setup');

$installer->startSetup();

try {
    $installer->getConnection()
        ->addColumn($installer->getTable('sales/quote_address'), 'summa_andreani_insurance_amount', array(
            'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'comment' => 'summa_andreani_insurance_amount',
            'scale'     => 2,
            'precision' => 12,
        ));
    $installer->getConnection()
        ->addColumn($installer->getTable('sales/quote_address'), 'base_summa_andreani_insurance_amount', array(
            'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'comment' => 'base_summa_andreani_insurance_amount',
            'scale'     => 2,
            'precision' => 12,
        ));
    $installer->getConnection()
        ->addColumn($installer->getTable('sales/order_address'), 'summa_andreani_insurance_amount', array(
            'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'comment' => 'summa_andreani_insurance_amount',
            'scale'     => 2,
            'precision' => 12,
        ));
    $installer->getConnection()
        ->addColumn($installer->getTable('sales/order_address'), 'base_summa_andreani_insurance_amount', array(
            'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'comment' => 'base_summa_andreani_insurance_amount',
            'scale'     => 2,
            'precision' => 12,
        ));
    $installer->getConnection()
        ->addColumn($installer->getTable('sales/order'), 'summa_andreani_insurance_amount', array(
            'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'comment' => 'summa_andreani_insurance_amount',
            'scale'     => 2,
            'precision' => 12,
        ));
    $installer->getConnection()
        ->addColumn($installer->getTable('sales/order'), 'base_summa_andreani_insurance_amount', array(
            'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'comment' => 'base_summa_andreani_insurance_amount',
            'scale'     => 2,
            'precision' => 12,
        ));
    $installer->getConnection()
        ->addColumn($installer->getTable('sales/order'), 'summa_andreani_insurance_amount_invoiced', array(
            'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'comment' => 'summa_andreani_insurance_amount_invoiced',
            'scale'     => 2,
            'precision' => 10,
        ));
    $installer->getConnection()
        ->addColumn($installer->getTable('sales/order'), 'base_summa_andreani_insurance_amount_invoiced', array(
            'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'comment' => 'base_summa_andreani_insurance_amount_invoiced',
            'scale'     => 2,
            'precision' => 10,
        ));
    $installer->getConnection()
        ->addColumn($installer->getTable('sales/invoice'), 'summa_andreani_insurance_amount', array(
            'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'comment' => 'summa_andreani_insurance_amount',
            'scale'     => 2,
            'precision' => 10,
        ));
    $installer->getConnection()
        ->addColumn($installer->getTable('sales/invoice'), 'base_summa_andreani_insurance_amount', array(
            'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'comment' => 'base_summa_andreani_insurance_amount',
            'scale'     => 2,
            'precision' => 10,
        ));
    $installer->getConnection()
        ->addColumn($installer->getTable('sales/order'), 'summa_andreani_insurance_amount_refunded', array(
            'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'comment' => 'summa_andreani_insurance_amount_refunded',
            'scale'     => 2,
            'precision' => 10,
        ));
    $installer->getConnection()
        ->addColumn($installer->getTable('sales/order'), 'base_summa_andreani_insurance_amount_refunded', array(
            'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'comment' => 'base_summa_andreani_insurance_amount_refunded',
            'scale'     => 2,
            'precision' => 10,
        ));
    $installer->getConnection()
        ->addColumn($installer->getTable('sales/creditmemo'), 'summa_andreani_insurance_amount', array(
            'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'comment' => 'summa_andreani_insurance_amount',
            'scale'     => 2,
            'precision' => 10,
        ));
    $installer->getConnection()
        ->addColumn($installer->getTable('sales/creditmemo'), 'base_summa_andreani_insurance_amount', array(
            'type'    => Varien_Db_Ddl_Table::TYPE_DECIMAL,
            'comment' => 'base_summa_andreani_insurance_amount',
            'scale'     => 2,
            'precision' => 10,
        ));
} catch(Exception $e) {}

$installer->endSetup();