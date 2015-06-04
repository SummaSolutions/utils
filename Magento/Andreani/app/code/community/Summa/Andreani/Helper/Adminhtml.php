<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        22/04/15
 * Time:        15:36
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */

class Summa_Andreani_Helper_Adminhtml
    extends Mage_Core_Helper_Abstract
{
    /**
     * Function to show button on admin shipment view to call Andreani Web service and get Constancy Link
     * @param $shipment Mage_Sales_Model_Order_Shipment
     * @return bool
     */
    public function canGenerateConstancy($shipment)
    {
        $order = $shipment->getOrder();
        if (!$this->_getHelperData()->isAndreaniShippingCarrier($order->getShippingCarrier())) {
            return false;
        }

        $tracks = $shipment->getAllTracks();
        if(empty($tracks)){
            return false;
        }

        if($shipment->getShippingLabel()) {
            return false;
        }
        return true;
    }

    /**
     * Function to show button on admin shipment view to call Andreani Web service and get Constancy Link
     * @param $shipment Mage_Sales_Model_Order_Shipment
     * @return bool
     */
    public function canCancelShipment($shipment)
    {
        $order = $shipment->getOrder();
        if (!$this->_getHelperData()->isAndreaniShippingCarrier($order->getShippingCarrier())) {
            return false;
        }
        if ($shipment->getOrder()->canShip()) {
            return false;
        }
        $tracks = $shipment->getAllTracks();
        if ($shipment->getSummaAndreaniShipmentStatus() == Summa_Andreani_Model_Status::SHIPMENT_NEW &&
            !empty($tracks)) {
            return true;
        }
        return false;
    }

    /**
     * @param $shipment Mage_Sales_Model_Order_Shipment
     * @return bool
     */
    public function canGenerateAndreaniRequest($shipment)
    {
        $result = true;
        $order = $shipment->getOrder();
        if (!$this->_getHelperData()->isAndreaniShippingCarrier($order->getShippingCarrier()))
        {
            return false;
        }
        if (!$order->getShippingCarrier()->isShipmentAvailable())
        {
            return false;
        }
        $tracks = $shipment->getAllTracks();
        if(!empty($tracks)){
            /** @var $track Mage_Sales_Model_Order_Shipment_Track */
            foreach ($tracks as $track) {
                if ( $this->_getHelperData()->isAndreaniCarrierCode($track->getCarrierCode()) ) {
                    $result = false;
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * @return Summa_Andreani_Helper_Data
     */
    protected function _getHelperData()
    {
        return Mage::helper('summa_andreani');
    }
}