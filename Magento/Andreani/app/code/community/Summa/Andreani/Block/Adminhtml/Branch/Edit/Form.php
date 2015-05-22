<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        26/03/15
 * Time:        16:51
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */

class Summa_Andreani_Block_Adminhtml_Branch_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Init class
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('summa_andreani_branch_form');
        $this->setTitle(Mage::helper('summa_andreani')->__('Branch Information'));
    }

    /**
     * Setup form fields for inserts/updates
     *
     * return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('summa_andreani_branch');

        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            'method'    => 'post'
        ));

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend'    => Mage::helper('summa_andreani')->__('Branch Information'),
            'class'     => 'fieldset-wide',
        ));

        if ($model->getId()) {
            $fieldset->addField('id', 'hidden', array(
                'name' => 'id',
            ));
        }

        $fieldset->addField('description', 'text', array(
            'name'      => 'description',
            'label'     => Mage::helper('summa_andreani')->__('Description'),
            'title'     => Mage::helper('summa_andreani')->__('Description'),
            'required'  => true,
        ));

        $fieldset->addField('branch_id', 'text', array(
            'name'      => 'branch_id',
            'label'     => Mage::helper('summa_andreani')->__('Branch ID'),
            'title'     => Mage::helper('summa_andreani')->__('Branch ID'),
            'class'     => 'validate-not-negative-number',
            'required'  => true,
        ));

        $fieldset->addField('address', 'text', array(
            'name'      => 'address',
            'label'     => Mage::helper('summa_andreani')->__('Address'),
            'title'     => Mage::helper('summa_andreani')->__('Address'),
            'required'  => true,
        ));

        $fieldset->addField('opening_hours', 'text', array(
            'name'      => 'time_attendance',
            'label'     => Mage::helper('summa_andreani')->__('Time Attendance'),
            'title'     => Mage::helper('summa_andreani')->__('Time Attendance'),
            'required'  => true,
        ));

        $fieldset->addField('lat', 'text', array(
            'name'      => 'lat',
            'label'     => Mage::helper('summa_andreani')->__('Latitude'),
            'title'     => Mage::helper('summa_andreani')->__('Latitude'),
            'required'  => true,
        ));

        $fieldset->addField('long', 'text', array(
            'name'      => 'long',
            'label'     => Mage::helper('summa_andreani')->__('Longitude'),
            'title'     => Mage::helper('summa_andreani')->__('Longitude'),
            'required'  => true,
        ));

        $fieldset->addField('email', 'text', array(
            'name'      => 'email',
            'label'     => Mage::helper('summa_andreani')->__('Email'),
            'title'     => Mage::helper('summa_andreani')->__('Email'),
            'class'     => 'validate-email',
            'required'  => true,
        ));

        $fieldset->addField('postal_code', 'text', array(
            'name'      => 'postal_code',
            'label'     => Mage::helper('summa_andreani')->__('Postal Code'),
            'title'     => Mage::helper('summa_andreani')->__('Postal Code'),
            'class'     => 'validate-not-negative-number',
            'required'  => true,
        ));

        $fieldset->addField('city', 'text', array(
            'name'      => 'city',
            'label'     => Mage::helper('summa_andreani')->__('City'),
            'title'     => Mage::helper('summa_andreani')->__('City'),
            'required'  => true,
        ));

        $fieldset->addField('region', 'text', array(
            'name'      => 'region',
            'label'     => Mage::helper('summa_andreani')->__('Region'),
            'title'     => Mage::helper('summa_andreani')->__('Region'),
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
            'label'     => Mage::helper('summa_andreani')->__('Region ID'),
            'title'     => Mage::helper('summa_andreani')->__('Region ID'),
            'required'  => true
        ));

        $fieldset->addField('enabled', 'select', array(
            'name'      => 'enabled',
            'values'   => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
            'label'     => Mage::helper('summa_andreani')->__('Enabled'),
            'title'     => Mage::helper('summa_andreani')->__('Enabled'),
            'required'  => true
        ));

        $fieldset->addField('phone_type_1', 'text', array(
            'name'      => 'phone_type_1',
            'label'     => Mage::helper('summa_andreani')->__('Kind Phone 1'),
            'title'     => Mage::helper('summa_andreani')->__('Kind Phone 1'),
            'required'  => true,
        ));

        $fieldset->addField('phone_1', 'text', array(
            'name'      => 'phone_1',
            'label'     => Mage::helper('summa_andreani')->__('Phone 1'),
            'title'     => Mage::helper('summa_andreani')->__('Phone 1'),
            'required'  => true,
        ));

        $fieldset->addField('phone_type_2', 'text', array(
            'name'      => 'phone_type_2',
            'label'     => Mage::helper('summa_andreani')->__('Kind Phone 2'),
            'title'     => Mage::helper('summa_andreani')->__('Kind Phone 2'),
            'required'  => false,
        ));

        $fieldset->addField('phone_2', 'text', array(
            'name'      => 'phone_2',
            'label'     => Mage::helper('summa_andreani')->__('Phone 2'),
            'title'     => Mage::helper('summa_andreani')->__('Phone 2'),
            'required'  => false,
        ));

        $fieldset->addField('phone_type_3', 'text', array(
            'name'      => 'phone_type_3',
            'label'     => Mage::helper('summa_andreani')->__('Kind Phone 3'),
            'title'     => Mage::helper('summa_andreani')->__('Kind Phone 3'),
            'required'  => false,
        ));

        $fieldset->addField('phone_3', 'text', array(
            'name'      => 'phone_3',
            'label'     => Mage::helper('summa_andreani')->__('Phone 3'),
            'title'     => Mage::helper('summa_andreani')->__('Phone 3'),
            'required'  => false,
        ));

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}