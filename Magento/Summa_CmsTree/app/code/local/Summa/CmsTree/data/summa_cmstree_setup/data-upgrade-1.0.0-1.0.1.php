<?php

$isSingleStoreMode = Mage::app()->isSingleStoreMode();
$stores = Mage::app()->getStores(!$isSingleStoreMode);

foreach ($stores as $store) {
    $storeId = $store->getId();
// Create default root page for this store
    $treePage = Mage::getModel('summa_cmstree/cmsTree');
    $treePage->setStoreId($store->getId());
    $treePage->setParentId(null);
    $treePage->setPageId(null);
    $treePage->setPosition(1);
    if ($storeId) {
        $treePage->setTitle($store->getName() . ' Root');
    } else {
        $treePage->setTitle('Default Root');
    }
    $treePage->save();
    $treePage->setPath($treePage->getId());
    $treePage->save();

}