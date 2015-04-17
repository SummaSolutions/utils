<?php

class Summa_CmsTree_Block_Adminhtml_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    public function __construct()
    {
        parent::__construct();
        $this->_blockGroup = 'summa_cmstree';
        $this->_controller = 'adminhtml';
        $this->_headerText = $this->__('Edit Node');
        $this->_removeButton('add');
        $this->_addButton('delete', array(
            'label' => $this->__('Delete'),
            'onclick' => 'setLocation(\' ' . $this->getDeleteUrl() . '\')',
            'class' => 'delete',
        ));
    }

    protected function _prepareLayout(){
        $this->_updateButton( 'save', 'label', $this->__( 'Update Node' ) );
        $this->_updateButton( 'delete', 'label', $this->__( 'Delete Node' ) );
        parent::_prepareLayout();
    }

    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/deleteNode', array('store' => $this->getRequest()->getParam('store'),'id'=>$this->getRequest()->getParam('id')));
    }
}