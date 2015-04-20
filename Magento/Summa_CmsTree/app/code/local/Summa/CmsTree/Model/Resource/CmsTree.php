<?php

class Summa_CmsTree_Model_Resource_CmsTree
    extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('summa_cmstree/cmsTree', 'id');
    }

    public function updatePosition($node, $parentNodeId, $position)
    {
        $table = $this->getTable('summa_cmstree/cmsTree');
        $adapter = $this->_getWriteAdapter();
        $positionField = $adapter->quoteIdentifier('position');

        $bind = array(
            'position' => new Zend_Db_Expr($positionField . ' - 1')
        );
        $where = array(
            'parent_id = ?' => $node->getParentId(),
            $positionField . ' > ?' => $node->getPosition()
        );
        $adapter->update($table, $bind, $where);

        $bind['position'] = new Zend_Db_Expr($positionField . ' + 1');
        $where = array(
            'parent_id = ?' => $parentNodeId,
            $positionField . ' >= ?' => $position
        );
        $adapter->update($table, $bind, $where);
    }

    public function updateChildrensPath($parentPath, $newPath)
    {
        $table = $this->getTable('summa_cmstree/cmsTree');
        $adapter = $this->_getWriteAdapter();
        $pathField = $adapter->quoteIdentifier('path');

        $bind = array(
            'path' => new Zend_Db_Expr("REPLACE($pathField , '$parentPath' , '$newPath' )")
        );
        $where = array(
            'path LIKE ?' => "{$parentPath}%"
        );
        $adapter->update($table, $bind, $where);
    }
}