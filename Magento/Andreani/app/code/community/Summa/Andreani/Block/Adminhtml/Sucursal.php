<?php
class Summa_Andreani_Block_Adminhtml_Sucursal
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_controller = 'adminhtml_sucursal';
        $this->_blockGroup = 'summa_andreani';
        $this->_headerText = $this->__('Branch Management');
        parent::__construct();
    }
}