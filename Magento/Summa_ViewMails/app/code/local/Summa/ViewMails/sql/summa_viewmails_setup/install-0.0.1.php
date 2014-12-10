<?php

$installer = $this;

$installer->startSetup();

/**
 * Create table 'sitemap'
 */
$table = $installer->getConnection()
    ->newTable($installer->getTable('summa_viewmails/email_archive'))
    ->addColumn('archive_id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
        ), 'Archive Id')
    ->addColumn('hash', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
    ), 'Email Archive Hash')
    ->addColumn('subject', Varien_Db_Ddl_Table::TYPE_TEXT, 255, array(
        ), 'Email Subject')
    ->addColumn('body', Varien_Db_Ddl_Table::TYPE_TEXT, '64k', array(
    ), 'Email Body')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
    ), 'Date of Email Archive Creation')
    ->setComment('Email Archive');

$installer->getConnection()->createTable($table);

$installer->endSetup();
