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
$entities = array('customer_address', 'quote_address', 'order_address');

$fields  = array(
    'dni'       => array(
        'type'                          => 'varchar',
        'label'                         => 'DNI',
        'input'                         => 'text',
        'required'                      => 0,
        'global'                        => 1,
        'visible'                       => 1,
        'user_defined'                  => 1,
        'searchable'                    => 0,
        'filterable'                    => 0,
        'comparable'                    => 0,
        'visible_on_front'              => 1,
        'visible_in_advanced_search'    => 0,
        'unique'                        => 0,
        'is_configurable'               => 0,
        'position'                      => 200
    ),
    'number'    => array(
        'type'                          => 'varchar',
        'label'                         => 'Number',
        'input'                         => 'text',
        'required'                      => 0,
        'global'                        => 1,
        'visible'                       => 1,
        'user_defined'                  => 1,
        'searchable'                    => 0,
        'filterable'                    => 0,
        'comparable'                    => 0,
        'visible_on_front'              => 1,
        'visible_in_advanced_search'    => 0,
        'unique'                        => 0,
        'is_configurable'               => 0,
        'position'                      => 201
    ),
    'floor'     => array(
        'type'                          => 'varchar',
        'label'                         => 'Floor',
        'input'                         => 'text',
        'required'                      => 0,
        'global'                        => 1,
        'visible'                       => 1,
        'user_defined'                  => 1,
        'searchable'                    => 0,
        'filterable'                    => 0,
        'comparable'                    => 0,
        'visible_on_front'              => 1,
        'visible_in_advanced_search'    => 0,
        'unique'                        => 0,
        'is_configurable'               => 0,
        'position'                      => 202
    ),
    'apartment' => array(
        'type'                          => 'varchar',
        'label'                         => 'Apartment',
        'input'                         => 'text',
        'required'                      => 0,
        'global'                        => 1,
        'visible'                       => 1,
        'user_defined'                  => 1,
        'searchable'                    => 0,
        'filterable'                    => 0,
        'comparable'                    => 0,
        'visible_on_front'              => 1,
        'visible_in_advanced_search'    => 0,
        'unique'                        => 0,
        'is_configurable'               => 0,
        'position'                      => 203
    )
);

foreach ($entities as $entity) {
    foreach ($fields as $code => $fieldOptions){
        $salesSetup->addAttribute($entity, $code, $fieldOptions );
    }
}

$fieldsets = array('customer_register_address',
    'customer_address_edit',
    'adminhtml_customer_address');

foreach($fields as $code => $fieldOptions){
    Mage::getModel('eav/config')
        ->getAttribute('customer_address', $code)
        ->setData('used_in_forms', $fieldsets)
        ->save();
}
$installer->endSetup();