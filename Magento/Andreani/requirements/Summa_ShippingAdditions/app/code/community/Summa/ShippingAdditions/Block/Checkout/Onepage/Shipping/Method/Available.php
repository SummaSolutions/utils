<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        13/05/15
 * Time:        14:47
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */

class Summa_ShippingAdditions_Block_Checkout_Onepage_Shipping_Method_Available
    extends Mage_Checkout_Block_Onepage_Shipping_Method_Available
{
    /**
     * Function to get Shipping Method Additional block if exist
     * @param $code
     *
     * @return string
     */
    public function getShippingMethodFormHtml($code)
    {
        return $this->_getHelper()->getShippingMethodFormHtml($this,$code);
    }

    /**
     * Function To get status of Show As Select based-on Carrier code
     * @param $carrierCode
     *
     * @return bool
     */
    public function showAsSelectShippingMethod($carrierCode)
    {
        return $this->_getHelper()->showAsSelectShippingMethod($carrierCode);
    }

    /**
     * Function to get Helper
     * @return Summa_ShippingAdditions_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('summa_shippingAdditions');
    }
}