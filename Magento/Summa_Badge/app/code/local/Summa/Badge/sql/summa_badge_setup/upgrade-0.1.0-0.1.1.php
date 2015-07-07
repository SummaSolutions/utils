<?php

$installer = $this;
$installer->startSetup();

$io = new Varien_Io_File();
$io->rmdir(Mage::getBaseDir('media').'/badges');
$io->mkdir(Mage::getBaseDir('media').'/wysiwyg/badges');

$installer->endSetup();