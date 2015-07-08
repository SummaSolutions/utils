<?php

/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        26/03/15
 * Time:        16:51
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */

class Summa_Andreani_Block_Adminhtml_Branch_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{

    public function __construct()
    {
        $this->_blockGroup = 'summa_andreani';
        $this->_controller = 'adminhtml_branch';

        parent::__construct();

        $this->_updateButton('save', 'label', Mage::helper('summa_andreani')->__('Save Branch'));
        $this->_updateButton('delete', 'label', Mage::helper('summa_andreani')->__('Delete Branch'));
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
        if (Mage::registry('branches_branch')->getId()) {
            return Mage::helper('summa_andreani')->__('Edit Branch');
        } else {
            return Mage::helper('summa_andreani')->__('New Branch');
        }
    }
}