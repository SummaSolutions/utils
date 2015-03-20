<?php

class Summa_Andreani_Block_Adminhtml_Sucursal_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSort('sucursal_id');
        $this->setId('sucursalGrid');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('summa_andreani/sucursal')->getCollection();
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

        $this->addColumn('descripcion',
            array(
                'header'=> $this->__('Descripcion'),
                'index' => 'descripcion'
            )
        );

        $this->addColumn('sucursal_id',
            array(
                'header'=> $this->__('Sucursal ID'),
                'index' => 'sucursal_id'
            )
        );

        $this->addColumn('direccion',
            array(
                'header'=> $this->__('Direccion'),
                'index' => 'direccion'
            )
        );

        $this->addColumn('horario',
            array(
                'header'=> $this->__('Horario'),
                'index' => 'horario'
            )
        );

        $this->addColumn('email',
            array(
                'header'=> $this->__('Email'),
                'index' => 'email'
            )
        );

        $this->addColumn('tipo_telefono_1',
            array(
                'header'=> $this->__('Tipo Telefono 1'),
                'index' => 'tipo_telefono_1'
            )
        );

        $this->addColumn('telefono_1',
            array(
                'header'=> $this->__('Telefono 1'),
                'index' => 'telefono_1'
            )
        );

        $this->addColumn('provincia',
            array(
                'header'=> $this->__('Provincia'),
                'index' => 'provincia'
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('sucursal');

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