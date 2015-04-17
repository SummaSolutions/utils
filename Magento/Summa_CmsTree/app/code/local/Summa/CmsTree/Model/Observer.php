<?php

class Summa_CmsTree_Model_Observer
{
    public function addToTopmenu($observer)
    {
        $menu = $observer->getMenu();

        $storeId = Mage::app()->getStore()->getStoreId();
        if (Mage::getStoreConfig('cmstree/general/is_visible')) {
            if (Mage::getStoreConfig('cmstree/use_default', $storeId)) {
                $storeId = 0;
            }
            $rootId = Mage::getModel('summa_cmstree/cmsTree')->getRootNodeId($storeId);
            $tree = Mage::getResourceSingleton('summa_cmstree/cmsTree_tree')
                ->setStoreId($storeId)
                ->load($rootId);
            $collection = Mage::getModel('summa_cmstree/cmsTree')->getCollection()
                ->addFieldToSelect('id')
                ->addFieldToSelect('page_id')
                ->addFieldToFilter('store_id', $storeId)
                ->join(array('pages' => 'cms/page'), 'main_table.page_id=pages.page_id');

            foreach ($tree->getNodes() as $node) {
                $page = $collection->getItemById($node->getId());
                if ($page) {
                    $node->setName($page->getTitle())
                        ->setUrl(Mage::getUrl() . $page->getIdentifier())
                        ->setId('category-node-' . $page->getId());
                    if ($node->getLevel() == 1) {
                        $menu->addChild($node);
                    }
                }
            }
        }
    }
}