<?php

class Summa_Badge_Model_Attribute_Source_Type
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $collection = Mage::getModel('summa_badge/badge')->getCollection();
            $items = array();

            $items[] = array(
                'label' => Mage::helper('summa_badge')->__('--Select Badge--'),
                'value' => 0
            );
            foreach ($collection as $item) {
                $items[] = array(
                    'label' => $item->getName(),
                    'value' => $item->getId()
                );
            }
            $this->_options = $items;
        }
        return $this->_options;
    }
}