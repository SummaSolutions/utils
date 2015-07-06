<?php
class Summa_Badge_Model_Badge
    extends Mage_Core_Model_Abstract
{
    protected $_eventPrefix = 'summa_badge';

    public function _construct()
    {
        $this->_init('summa_badge/badge');
    }

    public function _beforeSave()
    {

        $createdAt = $this->getCreatedAt();
        if (empty($createdAt)) {
            $this->setCreatedAt(Mage::getSingleton('core/date')->gmtDate());
        }
        $this->setUpdatedAt(Mage::getSingleton('core/date')->gmtDate());

        return parent::_beforeSave();
    }

}