<?php

class Summa_CmsTree_Block_Adminhtml_Tree
    extends Mage_Adminhtml_Block_Template
{
    public function __construct()
    {
        parent::__construct();
        $this->setUseAjax(true);
    }

    public function getRoot($parentNode = null, $recursionLevel = 3)
    {
        if (!is_null($parentNode) && $parentNode->getId()) {
            return $this->getNode($parentNode, $recursionLevel);
        }
        $root = Mage::registry('cmstree_root');
        if (is_null($root)) {
            $storeId = (int)$this->getStoreId();
            $rootId = Mage::getModel('summa_cmstree/cmsTree')->getRootNodeId($storeId);
            $tree = Mage::getResourceSingleton('summa_cmstree/cmsTree_tree')
                ->load(null, $recursionLevel);
            $root = $tree->getNodeById($rootId);
            if (!$root) {
                Mage::throwException('Could not retrieve root node of store ' . $storeId);
            } else {
                $root->setIsVisible(true);
                Mage::register('cmstree_root', $root);
            }
        }
        return $root;
    }

    public function getStoreId()
    {
        return $this->getRequest()->getParam('store');
    }

    public function getCollection()
    {
        $collection = $this->getData('cmstree_collection');
        if (is_null($collection)) {
            $collection = Mage::getModel('summa_cmstree/cmsTree')->getCollection();
            $collection->addFieldToFilter('store_id', $this->getStoreId());
            $this->setData('cmstree_collection', $collection);
        }
        return $collection;
    }

    protected function _isParentSelectedNode($node)
    {
        $selectedNode = Mage::registry('cmstree_currentnode');
        if ($node && $selectedNode) {
            $pathIds = $selectedNode->getPathIds();
            if (in_array($node->getId(), $pathIds)) {
                return true;
            }
        }
        return false;
    }

    public function getCurrentNodeId()
    {
        if ($node = Mage::registry('cmstree_currentnode')) {
            return $node->getId();
        }
        return Mage::getModel('summa_cmstree/cmsTree')->getRootNodeId($this->getStoreId());
    }

    public function getLoadTreeUrl($expanded = null)
    {
        return $this->getUrl('*/*/cmstreeJson');
    }

    public function getIsWasExpanded()
    {
        return Mage::getSingleton('admin/session')->getIsTreeWasExpanded() ? true : false;
    }

    public function getMoveUrl()
    {
        return $this->getUrl('*/*/move', array('store' => $this->getRequest()->getParam('store')));
    }


    public function getEditUrl()
    {
        return $this->getUrl('*/*/edit', array('store' => $this->getRequest()->getParam('store')));
    }


    public function getNode($node, $recursionLevel = 2)
    {
        $tree = Mage::getResourceSingleton('summa_cmstree/cmsTree_tree')
            ->load(null, $recursionLevel);
        return $tree->getNodeById($node->getId());
    }


    public function getTree($parenNodeCategory = null)
    {
        $rootArray = $this->_getNodeJson($this->getRoot($parenNodeCategory));
        $tree = isset($rootArray['children']) ? $rootArray['children'] : array();
        return $tree;
    }


    public function getTreeJson($parentNode = null)
    {
        $rootArray = $this->_getNodeJson($this->getRoot($parentNode));
        $json = Mage::helper('core')->jsonEncode(isset($rootArray['children']) ? $rootArray['children'] : array());
        return $json;
    }


    protected function _getNodeJson($node, $level = 0)
    {
        // create a node from data array
        $item = array();
        $item['text'] = $this->buildNodeName($node);
        $item['id'] = $node->getId();
        $item['store'] = (int)$this->getStoreId();
        $item['path'] = $node->getData('path');
        $item['cls'] = 'folder active-category';
        $allowMove = $this->_isNodeMoveable($node);
        $item['allowDrop'] = $allowMove;
        $item['allowDrag'] = $allowMove;
        $isParent = $this->_isParentSelectedNode($node);
        if ($node->hasChildren()) {
            $item['children'] = array();
            if (!($this->getUseAjax() && $node->getLevel() > 1 && !$isParent)) {
                foreach ($node->getChildren() as $child) {
                    $item['children'][] = $this->_getNodeJson($child, $level + 1);
                }
            }
        }
        if ($isParent || $node->getLevel() < 2) {
            $item['expanded'] = true;
        }
        return $item;
    }

    public function buildNodeName($node)
    {
        return $this->escapeHtml($node->getTitle());
    }

    protected function _isNodeMoveable($node)
    {
        return !Mage::getModel('summa_cmstree/cmsTree')->load($node->getId())->isRoot();
    }
}