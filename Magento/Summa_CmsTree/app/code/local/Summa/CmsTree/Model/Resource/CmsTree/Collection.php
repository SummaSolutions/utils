<?php

class Summa_CmsTree_Model_Resource_CmsTree_Collection
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    protected function _construct()
    {
        $this->_init('summa_cmstree/cmsTree');
    }

    public function addIdFilter($pageIds)
    {
        if (is_array($pageIds)) {
            if (empty($pageIds)) {
                $condition = '';
            } else {
                $condition = array('in' => $pageIds);
            }
        } elseif (is_numeric($pageIds)) {
            $condition = $pageIds;
        } elseif (is_string($pageIds)) {
            $ids = explode(',', $pageIds);
            if (empty($ids)) {
                $condition = $pageIds;
            } else {
                $condition = array('in' => $ids);
            }
        }
        $this->addFieldToFilter('id', $condition);

        return $this;
    }
}