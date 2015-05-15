<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        13/05/15
 * Time:        15:06
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */

class Summa_Andreani_Block_Shipping_Method_Abstract
    extends Mage_Core_Block_Template
{
    protected $serviceType = 'global';

    /**
     * Internal constructor, that is called from real constructor
     *
     */
    protected function _construct()
    {
        $this->setTemplate('summa/andreani/shippingMethod.phtml');
        parent::_construct();
    }

    public function getAdditionalDescription()
    {
        return $this->_getHelper()->__($this->_getHelper()->getConfigData('additional_description',$this->serviceType));
    }

    /**
     * @return Summa_Andreani_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('summa_andreani');
    }
}