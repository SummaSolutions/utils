<?php

class Summa_Badge_Block_Adminhtml_Badge_Edit
    extends Mage_Adminhtml_Block_Widget_Form_Container
{
    /**
     * Init class
     */
    public function __construct()
    {
        $this->_blockGroup = 'summa_badge';
        $this->_controller = 'adminhtml_badge';

        parent::__construct();

        $this->_updateButton('save', 'label', $this->__('Save Badge'));
        $this->_updateButton('delete', 'label', $this->__('Delete Badge'));
    }

    /**
     * Get Header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        $summaBadge = Mage::registry('summa_badge');
        if (is_object($summaBadge) && $summaBadge->getId()) {
            return $this->__('Edit Badge');
        } else {
            return $this->__('New Badge');
        }
    }
}