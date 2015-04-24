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
     * Action to generate constancy
     */
    public function generateLinkConstancyAction()
    {
        $shipmentId = $this->getRequest()->getParam('id');
        /** @var Mage_Sales_Model_Order_Shipment $shipment */
        $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);

        if(!$shipment->getId()){
            Mage::getSingleton('adminhtml/session')->addError($this->__('There was an error loading the shipment'));
            $this->_redirectReferer();
        }

        if (!Mage::helper('summa_andreani')->isAndreaniShippingCarrier($shipment->getOrder()->getShippingCarrier()))
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('The carrier is\'nt Andreani'));
            $this->_redirectReferer();
        }

        $tracks = $shipment->getAllTracks();
        if(empty($tracks)){
            Mage::getSingleton('adminhtml/session')->addError($this->__('No tracking numbers were found in the shipment'));
            $this->_redirectReferer();
        }

        /** @var Mage_Sales_Model_Order_Shipment_Track $track */
        foreach ($tracks as $track) {
            if (Mage::helper('summa_andreani')->isAndreaniCarrierCode($track->getCarrierCode())) {
                $constancyResponse = Mage::getSingleton('shipping/config')->getCarrierInstance($track->getCarrierCode())->getLinkConstancy($track->getNumber());

                if (isset($constancyResponse->ImprimirConstanciaResult)) {
                    /** @var $helper Summa_Andreani_Helper_Shipments */
                    $helper = Mage::helper('summa_andreani/shipments');
                    $response = new Varien_Object();
                    $response->setShippingLabelContent($helper->preparePdf($constancyResponse->ImprimirConstanciaResult->ResultadoImprimirConstancia->PdfLinkFile));
                    $helper->addShippingLabel($shipment,$response);

                    Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Successfully recovered constancy link from Andreani'));

                } else {
                    Mage::getSingleton('adminhtml/session')->addError($this->__('There was an error calling Andreani WebService'));
                }
            }
        }

        $this->_redirectReferer();
    }

    public function cancelShipmentAction()
    {
        $shipmentId = $this->getRequest()->getParam('id');
        /** @var Mage_Sales_Model_Order_Shipment $shipment */
        $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);

        if(!$shipment->getId()){
            Mage::getSingleton('adminhtml/session')->addError($this->__('There was an error loading the shipment'));
            $this->_redirectReferer();
        }

        if (!Mage::helper('summa_andreani')->isAndreaniShippingCarrier($shipment->getOrder()->getShippingCarrier()))
        {
            Mage::getSingleton('adminhtml/session')->addError($this->__('The carrier is\'nt Andreani'));
            $this->_redirectReferer();
        }

        $tracks = $shipment->getAllTracks();
        if(empty($tracks)){
            Mage::getSingleton('adminhtml/session')->addError($this->__('No tracking numbers were found in the shipment'));
            $this->_redirectReferer();
        }

        $cancelShipmentResponse = null;
        /** @var Mage_Sales_Model_Order_Shipment_Track $track */
        foreach ($tracks as $track) {
            if (Mage::helper('summa_andreani')->isAndreaniCarrierCode($track->getCarrierCode())) {
                $cancelShipmentResponse = Mage::getSingleton('shipping/config')->getCarrierInstance($this->getCarrierCode())->cancelShipmentRequest($track->getNumber());
            }
        }

        if (!is_null($cancelShipmentResponse) && isset($cancelShipmentResponse->AnularEnviosResult)) {
            Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Shipment was cancelled successfully'));
        } else {
            Mage::getSingleton('adminhtml/session')->addError($this->__('Could not cancel shipping'));
        }

        $this->_redirectReferer();
    }

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
        if (Mage::helper('summa_andreani')->isAndreaniShippingCarrier($carrier))
        {
            try {
                /** @var $carrier Summa_Andreani_Model_Shipping_Carrier_Abstract */
                $response = $carrier->doShipmentRequest($order,$shipment->getAllItems());

                if($response->hasErrors()){
                    Mage::helper('summa_andreani')->throwException($response->getErrors(),$carrier->getServiceType());
                }
                /** @var $helper Summa_Andreani_Helper_Shipments */
                $helper = Mage::helper('summa_andreani/shipments');
                $helper->addShippingLabel($shipment,$response);
                $helper->addTrackingCode($shipment,$response,$carrier);
            } catch (Exception $e) {
                Mage::getSingleton('core/session')->addError($e->getMessage());
                Mage::log($e->getMessage(), null, 'andreani.log');
            }
        } else {
            Mage::helper('summa_andreani')->throwException(Mage::helper('summa_andreani')->__('Can\'nt generate shipment, Order Shipping Carrier is\'nt an Andreani Carrier'));
        }
        $this->_redirectReferer();
    }

    public function generateShipmentsAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($orderId);
        Mage::helper('summa_andreani')->generateAndreaniShipment($order);


        $this->_redirect('*/sales_order/view', array('order_id' => $orderId));
    }
}