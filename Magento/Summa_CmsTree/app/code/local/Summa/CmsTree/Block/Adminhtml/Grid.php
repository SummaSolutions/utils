<?php

class Summa_CmsTree_Block_Adminhtml_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('page_id');
        $this->setUseAjax(true);
        $this->setFilterVisibility(true);
        $this->setPagerVisibility(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('cms/page')->getCollection();
        if ($this->getRequest()->getParam('store') != null) {
            $collection->addStoreFilter($this->getRequest()->getParam('store'));
        }
        $collection->setFirstStoreFlag(true);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    protected function _prepareColumns()
    {
        $this->addColumn('page_id', array(
            'header' => $this->__('ID'),
            'sortable' => true,
            'width' => '60',
            'index' => 'page_id'
        ));
        $this->addColumn('title', array(
            'header' => $this->__('Title'),
            'sortable' => true,
            'width' => '60',
            'index' => 'title'
        ));
        $this->addColumn('identifier', array(
            'header' => $this->__('URL Key'),
            'align' => 'left',
            'index' => 'identifier'
        ));
        $this->addColumn('root_template', array(
            'header' => $this->__('Layout'),
            'index' => 'root_template',
            'type' => 'options',
            'options' => Mage::getSingleton('page/source_layout')->getOptions(),
        ));

        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn('store', array(
                'header'        => $this->__('Store View'),
                'index'         => 'store_id',
                'type'          => 'store',
                'store_all'     => true,
                'store_view'    => true,
                'sortable'      => false,
            ));
        }

        $this->addColumn('is_active', array(
            'header' => $this->__('Status'),
            'index' => 'is_active',
            'type' => 'options',
            'options' => Mage::getSingleton('cms/page')->getAvailableStatuses()
        ));
        return parent::_prepareColumns();
    }

    protected function _prepareMassAction()
    {
        $this->setMassactionIdField('page_id');
        $this->getMassactionBlock()->setFormFieldName('page_id');
        $this->getMassactionBlock()->addItem('addNode', array(
            'label' => $this->__('Add Node to Tree'),
            'url' => $this->getUrl('*/*/massAddNode'),
            'additional' => array(
                'current_node' => array(
                    'name' => 'current_node',
                    'type' => 'hidden',
                    'label' => '',
                ),
                'store_id' => array(
                    'name' => 'store',
                    'type' => 'hidden',
                    'class' => 'hidden',
                    'label' => '',
                    'value' => (int)$this->getRequest()->getParam('store')
                )
            )
        ));
        return $this;
    }

    protected function _afterLoadCollection()
    {
        $this->getCollection()->walk('afterLoad');
        parent::_afterLoadCollection();
    }
}