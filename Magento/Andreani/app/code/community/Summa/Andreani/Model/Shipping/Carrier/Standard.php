<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        19/03/15
 * Time:        09:24
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */

class Summa_Andreani_Model_Shipping_Carrier_Standard
    extends Summa_Andreani_Model_Shipping_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'andreani_standard';
    protected $_serviceType = 'standard';
    protected $_shippingTypeForMatrixrates = 'Standard';

    public function isTrackingAvailable()
    {
        return true;
    }

    public function getAllowedMethods()
    {
        return array($this->getConfigData('title')=>$this->getConfigData('name'));
    }

}