<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        26/03/15
 * Time:        16:51
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */

class Summa_Andreani_Block_Adminhtml_Branch_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSort('branch_id');
        $this->setId('branchGrid');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('summa_andreani/branch')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id',
            array(
                'header'=> $this->__('ID'),
                'align' =>'right',
                'width' => '50px',
                'index' => 'id'
            )
        );

        $this->addColumn('description',
            array(
                'header'=> $this->__('Description'),
                'index' => 'description'
            )
        );

        $this->addColumn('branch_id',
            array(
                'header'=> $this->__('Branch ID'),
                'index' => 'branch_id'
            )
        );

        $this->addColumn('address',
            array(
                'header'=> $this->__('Address'),
                'index' => 'address'
            )
        );

        $this->addColumn('time_attendance',
            array(
                'header'=> $this->__('Time Attendance'),
                'index' => 'time_attendance'
            )
        );

        $this->addColumn('email',
            array(
                'header'=> $this->__('Email'),
                'index' => 'email'
            )
        );

        $this->addColumn('kind_phone_1',
            array(
                'header'=> $this->__('Kind Phone 1'),
                'index' => 'kind_phone_1'
            )
        );

        $this->addColumn('phone_1',
            array(
                'header'=> $this->__('Phone 1'),
                'index' => 'phone_1'
            )
        );

        $this->addColumn('region',
            array(
                'header'=> $this->__('Region'),
                'index' => 'region'
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('branch');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('summa_andreani')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('summa_andreani')->__('Are you sure?')
        ));
        
        return $this;
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
}