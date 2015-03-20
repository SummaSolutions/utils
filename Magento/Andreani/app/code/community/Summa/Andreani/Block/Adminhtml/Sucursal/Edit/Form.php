<?php

class Summa_Andreani_Block_Adminhtml_Sucursal_Edit_Form extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Init class
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('summa_andreani_sucursal_form');
        $this->setTitle($this->__('Informacion de Sucursal'));
    }

    /**
     * Setup form fields for inserts/updates
     *
     * return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('summa_andreani');

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method'    => 'post'
        ));

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('checkout')->__('Informacion de Sucursal'),
            'class'     => 'fieldset-wide',
        ));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name' => 'id',
            ));
        }

        $fieldset->addField('descripcion', 'text', array(
            'name'      => 'descripcion',
            'label'     => Mage::helper('checkout')->__('Descripcion'),
            'title'     => Mage::helper('checkout')->__('Descripcion'),
            'required'  => true,
        ));

        $fieldset->addField('sucursal_id', 'text', array(
            'name'      => 'sucursal_id',
            'label'     => Mage::helper('checkout')->__('Sucursal ID'),
            'title'     => Mage::helper('checkout')->__('Sucursal ID'),
            'class'     => 'validate-not-negative-number',
            'required'  => true,
        ));

        $fieldset->addField('direccion', 'text', array(
            'name'      => 'direccion',
            'label'     => Mage::helper('checkout')->__('Direccion'),
            'title'     => Mage::helper('checkout')->__('Direccion'),
            'required'  => true,
        ));

        $fieldset->addField('horario', 'text', array(
            'name'      => 'horario',
            'label'     => Mage::helper('checkout')->__('Horario'),
            'title'     => Mage::helper('checkout')->__('Horario'),
            'required'  => true,
        ));

        $fieldset->addField('latitud', 'text', array(
            'name'      => 'latitud',
            'label'     => Mage::helper('checkout')->__('Latitud'),
            'title'     => Mage::helper('checkout')->__('Latitud'),
            'required'  => true,
        ));

        $fieldset->addField('longitud', 'text', array(
            'name'      => 'longitud',
            'label'     => Mage::helper('checkout')->__('Longitud'),
            'title'     => Mage::helper('checkout')->__('Longitud'),
            'required'  => true,
        ));

        $fieldset->addField('email', 'text', array(
            'name'      => 'email',
            'label'     => Mage::helper('checkout')->__('Email'),
            'title'     => Mage::helper('checkout')->__('Email'),
            'class'     => 'validate-email',
            'required'  => true,
        ));

        $fieldset->addField('codigo_postal', 'text', array(
            'name'      => 'codigo_postal',
            'label'     => Mage::helper('checkout')->__('Codigo Postal'),
            'title'     => Mage::helper('checkout')->__('Codigo Postal'),
            'class'     => 'validate-not-negative-number',
            'required'  => true,
        ));

        $fieldset->addField('localidad', 'text', array(
            'name'      => 'localidad',
            'label'     => Mage::helper('checkout')->__('Localidad'),
            'title'     => Mage::helper('checkout')->__('Localidad'),
            'required'  => true,
        ));

        $fieldset->addField('provincia', 'text', array(
            'name'      => 'provincia',
            'label'     => Mage::helper('checkout')->__('Provincia'),
            'title'     => Mage::helper('checkout')->__('Provincia'),
            'required'  => true,
        ));

        $regions = Mage::getModel('directory/region')->getCollection()->addFieldToFilter('country_id', array("eq"=>'AR'));
        $regionOptions =array();
        foreach($regions as $region) {
            array_push($regionOptions, array('value'=>$region->getRegionId(), 'label'=>$region->getDefaultName()));
        }
        $fieldset->addField('region_id', 'select', array(
            'name'      => 'region_id',
            'values'   => $regionOptions,
            'label'     => Mage::helper('checkout')->__('Region'),
            'title'     => Mage::helper('checkout')->__('Region'),
            'required'  => true
        ));

        $fieldset->addField('enabled', 'select', array(
            'name'      => 'enabled',
            'values'   => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
            'label'     => Mage::helper('checkout')->__('Enabled'),
            'title'     => Mage::helper('checkout')->__('Enabled'),
            'required'  => true
        ));

        $fieldset->addField('tipo_telefono_1', 'text', array(
            'name'      => 'tipo_telefono_1',
            'label'     => Mage::helper('checkout')->__('Tipo Telefono 1'),
            'title'     => Mage::helper('checkout')->__('Tipo Telefono 1'),
            'required'  => true,
        ));

        $fieldset->addField('telefono_1', 'text', array(
            'name'      => 'telefono_1',
            'label'     => Mage::helper('checkout')->__('Telefono 1'),
            'title'     => Mage::helper('checkout')->__('Telefono 1'),
            'required'  => true,
        ));

        $fieldset->addField('tipo_telefono_2', 'text', array(
            'name'      => 'tipo_telefono_2',
            'label'     => Mage::helper('checkout')->__('Tipo Telefono 2'),
            'title'     => Mage::helper('checkout')->__('Tipo Telefono 2'),
            'required'  => false,
        ));

        $fieldset->addField('telefono_2', 'text', array(
            'name'      => 'telefono_2',
            'label'     => Mage::helper('checkout')->__('Telefono 2'),
            'title'     => Mage::helper('checkout')->__('Telefono 2'),
            'required'  => false,
        ));

        $fieldset->addField('tipo_telefono_3', 'text', array(
            'name'      => 'tipo_telefono_3',
            'label'     => Mage::helper('checkout')->__('Tipo Telefono 3'),
            'title'     => Mage::helper('checkout')->__('Tipo Telefono 3'),
            'required'  => false,
        ));

        $fieldset->addField('telefono_3', 'text', array(
            'name'      => 'telefono_3',
            'label'     => Mage::helper('checkout')->__('Telefono 3'),
            'title'     => Mage::helper('checkout')->__('Telefono 3'),
            'required'  => false,
        ));

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}