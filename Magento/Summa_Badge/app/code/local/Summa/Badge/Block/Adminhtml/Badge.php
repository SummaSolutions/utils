<?php

class Summa_Badge_Block_Adminhtml_Badge
    extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    public function __construct()
    {
        $this->_blockGroup = 'summa_badge';
        $this->_controller = 'adminhtml_badge';
        $this->_headerText = $this->__('Badges Grid');

        parent::__construct();
    }
}