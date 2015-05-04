<?php

class Summa_CmsTree_Model_CmsTree
    extends Mage_Core_Model_Abstract
{

    protected function _construct()
    {
        $this->_init('summa_cmstree/cmsTree');
    }


    public function getRootNodeId($storeId = null)
    {
        return $this->getRootNode($storeId)->getId();
    }


    public function getRootNode($storeId = null)
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('parent_id', array('null' => true));
        return $collection->getFirstItem();
    }

    public function getChildrens()
    {
        $collection = $this->getCollection()->addFieldToFilter('parent_id', $this->getId());
        return $collection;
    }


    public function delete()
    {
        foreach ($this->getChildrens() as $node) {
            $node->delete();
        }

        parent::delete($this);
        return $this;
    }

    public function save()
    {
        if ($this->getParentId() == null) {
            $collection = $this->getCollection()
                ->addFieldToFilter('store_id', $this->getStoreId())
                ->addFieldToFilter('parent_id', null);
            if ($collection->count()) {
                throw new Exception('Parent ID can not be null. Root Node already exists!');
            }
        }

        parent::save($this);
        return $this;
    }

    public function move($parentId, $prevNodeId)
    {
        /**
         * Validate new parent node id
         */

        $parent = Mage::getModel('summa_cmstree/cmsTree')
            ->load($parentId);

        if (!$parent->getId()) {
            Mage::throwException(
                Mage::helper('summa_cmstree')->__('Cms Tree Node move operation is not possible: the new parent node was not found.')
            );
        }

        if (!$this->getId()) {
            Mage::throwException(
                Mage::helper('summa_cmstree')->__('Cms Tree Node move operation is not possible: the current node was not found.')
            );
        } elseif ($parent->getId() == $this->getId()) {
            Mage::throwException(
                Mage::helper('summa_cmstree')->__('Cms Tree Node move operation is not possible: parent node is equal to child node.')
            );
        }

        $resource = $this->_getResource();
        $prevNode = Mage::getModel('summa_cmstree/cmsTree')
            ->load($prevNodeId);

        $position = 1;
        if ($prevNode->getId()) {
            $position = $prevNode->getPosition() + 1;
        }

        $resource->updatePosition($this, $parentId, $position);
        $this->setPosition($position);
        $oldChildrensPath = $this->getPath() . '/';
        $this->setPath($parent->getPath() . '/' . $this->getId());
        $newChildrensPath = $this->getPath() . '/';
        $resource->updateChildrensPath($oldChildrensPath, $newChildrensPath);

        $this->setParentId($parent->getId());
        $level = $parent->getLevel();
        $this->setLevel(++$level);
        $this->save();

        return $this;
    }

    public function getPathIds()
    {
        $ids = $this->getData('path_ids');
        if (is_null($ids)) {
            $ids = explode('/', $this->getPath());
            $this->setData('path_ids', $ids);
        }
        return $ids;
    }

    public function getLastPosition()
    {
        $collection = $this->getCollection()
            ->addFieldToFilter('parent_id', $this->getId())
            ->setOrder('position', 'ASC');
        return $collection->count() ? $collection->getLastItem()->getPosition() : 0;
    }

    public function isRoot()
    {
        return $this->getParentId() ? false : true;
    }
}