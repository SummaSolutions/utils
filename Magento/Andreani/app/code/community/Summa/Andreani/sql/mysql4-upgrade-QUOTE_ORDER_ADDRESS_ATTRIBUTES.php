<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        18/02/15
 * Time:        11:58
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */

$installer = $this;
/* @var $salesSetup Mage_Sales_Model_Resource_Setup */
$salesSetup = Mage::getResourceModel('sales/setup', 'core_setup');

$installer->startSetup();
$entities = array('quote_address', 'order_address');

$fields  = array(
    'dni',
    'number',
    'floor',
    'apartment'
);
$options = array('type' => Varien_Db_Ddl_Table::TYPE_VARCHAR);

foreach ($entities as $entity) {
    foreach ($fields as $field){
        $salesSetup->addAttribute($entity, $field, $options );
    }
}
$installer->endSetup();