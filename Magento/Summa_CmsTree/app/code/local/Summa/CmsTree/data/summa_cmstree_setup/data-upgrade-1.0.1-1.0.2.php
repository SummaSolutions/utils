<?php
$isSingleStoreMode = Mage::app()->isSingleStoreMode();
$stores = Mage::app()->getStores(!$isSingleStoreMode);

foreach ($stores as $store) {
    $storeId = $store->getId();
    if ($storeId) {
// Create config node
        Mage::getConfig()->saveConfig('cmstree/use_default', 1, 'stores', $store->getId());
    }
}