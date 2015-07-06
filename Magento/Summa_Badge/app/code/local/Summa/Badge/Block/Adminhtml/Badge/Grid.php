<?php

class Summa_Badge_Block_Adminhtml_Badge_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setDefaultSort('id');
        $this->setId('summa_badge_grid');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }


    protected function _prepareCollection()
    {

        $collection = Mage::getModel('summa_badge/badge')->getCollection();
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('id',
            array(
                'header' => $this->__('ID'),
                'align'  => 'right',
                'width'  => '50px',
                'index'  => 'badge_id'
            )
        );

        $this->addColumn('name',
            array(
                'header' => $this->__('Name'),
                'index'  => 'name'
            )
        );

        $this->addColumn('created_at', array(
            'header'    => Mage::helper('summa_badge')->__('Created At'),
            'width'     => 200,
            'type'      => 'datetime',
            'align'     => 'center',
            'index'     => 'created_at',
            'gmtoffset' => true
        ));

        $this->addColumn('updated_at', array(
            'header'    => Mage::helper('summa_badge')->__('Updated At'),
            'width'     => 200,
            'type'      => 'datetime',
            'align'     => 'center',
            'index'     => 'updated_at',
            'gmtoffset' => true
        ));

        $this->addColumn('url',
            array(
                'header' => $this->__('Url'),
                'index'  => 'url'
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('badge_id');
        $this->getMassactionBlock()->setFormFieldName('badges_ids');

        $this->getMassactionBlock()->addItem('delete', array(
            'label'   => $this->__('Delete'),
            'url'     => $this->getUrl('*/*/massDelete'),
            'confirm' => $this->__('Are you sure?')
        ));

        return $this;
    }

    public function getRowUrl($row)
    {

        return $this->getUrl('*/*/edit', array('badge_id' => $row->getId()));
    }
}