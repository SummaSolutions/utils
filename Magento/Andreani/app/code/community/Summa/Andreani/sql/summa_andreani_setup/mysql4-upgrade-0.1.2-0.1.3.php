<?php

$installer = $this;
$installer->startSetup();

$installer->run("
ALTER TABLE {$this->getTable('sales_flat_order_address')} ADD COLUMN `andreani_branch_id` INT DEFAULT NULL;"
);
$installer->run("
ALTER TABLE {$this->getTable('sales_flat_quote_address')} ADD COLUMN `andreani_branch_id` INT DEFAULT NULL;"
);

$installer->endSetup();