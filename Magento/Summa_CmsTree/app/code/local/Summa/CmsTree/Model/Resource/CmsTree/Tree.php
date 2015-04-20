<?php

class Summa_CmsTree_Model_Resource_CmsTree_Tree
    extends Varien_Data_Tree_Dbp
{
    public function __construct()
    {
        $resource = Mage::getSingleton('core/resource');
        parent::__construct(
            $resource->getConnection('write'),
            $resource->getTableName('cms_tree'),
            array(
                Varien_Data_Tree_Dbp::ID_FIELD => 'id',
                Varien_Data_Tree_Dbp::PATH_FIELD => 'path',
                Varien_Data_Tree_Dbp::ORDER_FIELD => 'position',
                Varien_Data_Tree_Dbp::LEVEL_FIELD => 'level',
            )
        );
    }
}