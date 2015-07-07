<?php

$installer = $this;
$installer->startSetup();

$io = new Varien_Io_File();
$io->mkdir(Mage::getBaseDir('media').'/badges');

$installer->endSetup();