<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        22/04/15
 * Time:        11:46
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */

/** @var Mage_Core_Model_Resource_Setup $installer */
$installer = $this;
$installer->startSetup();

try {
    /** @var Mage_Sales_Model_Order_Status $statusNew */
    $statusNew = Mage::getModel('sales/order_status')
        ->setStatus(Summa_Andreani_Model_Status::ORDER_STATUS_NEW)
        ->setLabel('Andreani Shipping New')
        ->save();
    $statusNew->assignState('complete');

    /** @var Mage_Sales_Model_Order_Status $statusProcessing */
    $statusProcessing = Mage::getModel('sales/order_status')
        ->setStatus(Summa_Andreani_Model_Status::ORDER_STATUS_PROCESSING)
        ->setLabel('Andreani Shipping Processing')
        ->save();
    $statusProcessing->assignState('complete');

    /** @var Mage_Sales_Model_Order_Status $statusCompleted */
    $statusCompleted = Mage::getModel('sales/order_status')
        ->setStatus(Summa_Andreani_Model_Status::ORDER_STATUS_COMPLETED)
        ->setLabel('Andreani Shipping Completed')
        ->save();
    $statusCompleted->assignState('complete');

    /** @var Mage_Sales_Model_Order_Status $statusPending */
    $statusPending = Mage::getModel('sales/order_status')
        ->setStatus(Summa_Andreani_Model_Status::ORDER_STATUS_PENDING)
        ->setLabel('Andreani Shipping Pending')
        ->save();
    $statusPending->assignState('complete');

    /** @var Mage_Sales_Model_Order_Status $statusFailed */
    $statusFailed = Mage::getModel('sales/order_status')
        ->setStatus(Summa_Andreani_Model_Status::ORDER_STATUS_FAILED)
        ->setLabel('Andreani Shipping Failed')
        ->save();
    $statusFailed->assignState('complete');
} catch(Exception $e) {}

$installer->endSetup();