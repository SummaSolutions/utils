<?php
class Summa_Andreani_Block_Adminhtml_Sucursal_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_blockGroup = 'summa_andreani';
        $this->_controller = 'adminhtml_sucursal';

        parent::__construct();

        $this->_updateButton('save', 'label', $this->__('Guardar Sucursal'));
        $this->_updateButton('delete', 'label', $this->__('Borrar Sucursal'));
        $this->_addButton('saveandcontinue', array(
            'label'     => Mage::helper('adminhtml')->__('Save And Continue Edit'),
            'onclick'   => 'saveAndContinueEdit()',
            'class'     => 'save',
        ), -100);

        $this->_formScripts[] = "
            function saveAndContinueEdit(){
                editForm.submit($('edit_form').action+'back/edit/');
            }
        ";

    }

    public function getHeaderText()
    {
        if (Mage::registry('sucursales_sucursal')->getId()) {
            return $this->__('Edit Brand');
        } else {
            return $this->__('New Brand');
        }
    }
}