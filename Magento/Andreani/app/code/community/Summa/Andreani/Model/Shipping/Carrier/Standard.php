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

    protected $_code = 'andreaniStandard';
    protected $_serviceType = 'standard';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'summa_andreani_standard';
    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'andreani_standard';

    public function isTrackingAvailable()
    {
        return true;
    }

    public function getAllowedMethods()
    {
        return array($this->_code=>$this->getConfigData('name'));
    }

}