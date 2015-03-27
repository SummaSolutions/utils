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

$installer->run("
    ALTER TABLE {$this->getTable('sales_flat_order_address')} ADD COLUMN `summa_andreani_insurance` DECIMAL DEFAULT NULL;"
);
$installer->run("
    ALTER TABLE {$this->getTable('sales_flat_quote_address')} ADD COLUMN `summa_andreani_insurance` DECIMAL DEFAULT NULL;"
);

$installer->endSetup();