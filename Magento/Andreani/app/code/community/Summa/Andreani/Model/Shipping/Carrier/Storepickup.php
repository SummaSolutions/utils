<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        19/03/15
 * Time:        09:24
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */

class Summa_Andreani_Model_Shipping_Carrier_Storepickup
    extends Summa_Andreani_Model_Shipping_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'andreani_storepickup';
    protected $_serviceType = 'storepickup';
    protected $_shippingTypeForMatrixrates = 'Storepickup';

    public function isTrackingAvailable()
    {
        return true;
    }

    public function getAllowedMethods()
    {
        return array($this->getConfigData('title')=>$this->getConfigData('name'));
    }

    /**
     * Function to get Standard Rate
     * @return Mage_Shipping_Model_Rate_Result_Method
     */
    protected function _getStandardRate()
    {
        /** @var $rate Mage_Shipping_Model_Rate_Result_Method */
        $rate = Mage::getModel('shipping/rate_result_method');

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($this->_getHelper()->getConfigData('title'));
        $rate->setMethod($this->_code);
        $rate->setMethodTitle($this->_getHelper()->getConfigData('name'));

        // Starts at zero cost, calculates after picking a store
        $rate->setPrice(0);// TODO: look for another solution
        $rate->setCost(0);

        return $rate;
    }
}