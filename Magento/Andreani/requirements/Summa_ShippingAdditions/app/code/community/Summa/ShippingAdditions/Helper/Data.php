<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        15/05/15
 * Time:        14:06
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */

class Summa_ShippingAdditions_Helper_Data
    extends Mage_Core_Helper_Abstract
{
    /**
     * Function to get Shipping Method Additional block if exist
     * @param Mage_Core_Block_Template $block
     * @param $code
     *
     * @return string
     */
    public function getShippingMethodFormHtml($block,$code)
    {
        return $block->getChildHtml('shipment.method.' . $code);
    }

    /**
     * Function To get status of Show As Select based-on Carrier code
     * @param $carrierCode
     *
     * @return bool
     */
    public function showAsSelectShippingMethod($carrierCode)
    {
        return Mage::getStoreConfigFlag('carriers/'.$carrierCode.'/show_as_select');
    }
}