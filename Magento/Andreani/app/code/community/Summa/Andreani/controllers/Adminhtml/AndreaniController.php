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
            Mage::getSingleton('adminhtml/session')->addError($this->__('The carrier isn\'t Andreani'));
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

                    Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('summa_andreani')->__('Successfully recovered constancy link from Andreani for tracking %s',$track->getNumber()));

                } else {
                    Mage::getSingleton('adminhtml/session')->addError(Mage::helper('summa_andreani')->__('There was an error calling Andreani WebService on tracking %s',$track->getNumber()));
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
            Mage::getSingleton('adminhtml/session')->addError($this->__('The carrier isn\'t Andreani'));
            $this->_redirectReferer();
        }

        $tracks = $shipment->getAllTracks();
        if(empty($tracks)){
            Mage::getSingleton('adminhtml/session')->addError($this->__('No tracking numbers were found in the shipment'));
            $this->_redirectReferer();
        }

        $cancelShipmentResponse = array();
        /** @var Mage_Sales_Model_Order_Shipment_Track $track */
        foreach ($tracks as $track) {
            if (Mage::helper('summa_andreani')->isAndreaniCarrierCode($track->getCarrierCode())) {
                $cancelShipmentResponse[$track->getNumber()] = Mage::getSingleton('shipping/config')->getCarrierInstance($this->getCarrierCode())->cancelShipmentRequest($track->getNumber());
            }
        }

        foreach ($cancelShipmentResponse as $trackId => $response) {
            if (!$response->hasErrors() && $response->getCanceledShipment()) {
                Mage::getSingleton('adminhtml/session')->addSuccess($this->__('Shipment with tracking number %s was cancelled successfully',$trackId));
                $track->delete();
            } else {
                Mage::getSingleton('adminhtml/session')->addError($this->__('Could not cancel Shipment with tracking number %s',$trackId));
            }
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
            Mage::helper('summa_andreani')->throwException(Mage::helper('summa_andreani')->__('The carrier isn\'t Andreani'));
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