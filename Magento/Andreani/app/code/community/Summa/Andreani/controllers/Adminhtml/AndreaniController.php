<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        16/04/15
 * Time:        15:18
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */
class Summa_Andreani_Adminhtml_AndreaniController
    extends Mage_Adminhtml_Controller_Action
{
    /**
     * Action to generate Andreani Request on already created shipments
     * @throws Mage_Adminhtml_Exception
     */
    public function generateAndreaniRequestAction()
    {
        $shipmentId = $this->getRequest()->getParam('id');
        /** @var Mage_Sales_Model_Order_Shipment $shipment */
        $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
        $order = $shipment->getOrder();
        $carrier = $order->getShippingCarrier();
        if ($carrier instanceof Summa_Andreani_Model_Shipping_Carrier_Abstract)
        {
            try {
                $response = $carrier->doShipmentRequest($order,$shipment->getAllItems());

                if($response->hasErrors()){
                    Mage::helper('summa_andreani')->throwException($response->getErrors(),$carrier->getServiceType());
                }
                $track = Mage::getModel('sales/order_shipment_track');
                $track->setCarrierCode($carrier->getCode())
                    ->setTitle($response->getShippingLabelContent())
                    ->setNumber($response->getTrackingNumber());
                $shipment->addTrack($track);
                $shipment->save();
            } catch (Exception $e) {
                Mage::getSingleton('core/session')->addError($e->getMessage());
                Mage::log($e->getMessage(), null, 'andreani.log');
            }
        } else {
            Mage::helper('summa_andreani')->throwException();
        }
        $this->_redirectReferer();
    }
}