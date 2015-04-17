<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        26/03/15
 * Time:        16:51
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */

class Summa_Andreani_Block_Adminhtml_Branch
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_branch';
        $this->_blockGroup = 'summa_andreani';
        $this->_headerText = $this->__('Branch Management');
        parent::__construct();


        if($this->_isAllowed('fetchBranches')){
            $this->_addButton('fetchBranches', array(
                'label'     => Mage::helper('summa_andreani')->__('Fetch Branches from Andreani Web Service'),
                'onclick'   => 'setLocation(\'' . $this->getFetchBranchesUrl() . '\')',
            ));
        }
    }

    public function getFetchBranchesUrl()
    {
        return $this->getUrl('*/*/fetchBranches');
    }

    protected function _isAllowed($action)
    {
        return Mage::getSingleton('admin/session')->isAllowed('summa_andreani/adminhtml_branch_'.$action);
    }
}