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
            Mage::getSingleton('adminhtml/session')->addError($this->_getAndreaniHelper()->__('There was an error loading the shipment'));
            $this->_redirectReferer();
        }

        if (!$this->_getAndreaniHelper()->isAndreaniShippingCarrier($shipment->getOrder()->getShippingCarrier()))
        {
            Mage::getSingleton('adminhtml/session')->addError($this->_getAndreaniHelper()->__('The carrier isn\'t Andreani'));
            $this->_redirectReferer();
        }

        $tracks = $shipment->getAllTracks();
        if(empty($tracks)){
            Mage::getSingleton('adminhtml/session')->addError($this->_getAndreaniHelper()->__('No tracking numbers were found in the shipment'));
            $this->_redirectReferer();
        }

        /** @var Mage_Sales_Model_Order_Shipment_Track $track */
        foreach ($tracks as $track) {
            if ($this->_getAndreaniHelper()->isAndreaniCarrierCode($track->getCarrierCode())) {
                $constancyResponse = Mage::getSingleton('shipping/config')->getCarrierInstance($track->getCarrierCode())->getLinkConstancy($track->getNumber());

                if (!$constancyResponse->hasErrors()) {
                    /** @var $helper Summa_Andreani_Helper_Shipments */
                    $helper = Mage::helper('summa_andreani/shipments');
                    $response = new Varien_Object();
                    $response->setShippingLabelContent($helper->preparePdf($constancyResponse->getConstancyUrl()));
                    $helper->addShippingLabel($shipment,$response);

                    Mage::getSingleton('adminhtml/session')->addSuccess($this->_getAndreaniHelper()->__('Successfully recovered constancy link from Andreani for tracking %s',$track->getNumber()));

                } else {
                    Mage::getSingleton('adminhtml/session')->addError($this->_getAndreaniHelper()->__('There was an error calling Andreani WebService on tracking %s',$track->getNumber()));
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
            Mage::getSingleton('adminhtml/session')->addError($this->_getAndreaniHelper()->__('There was an error loading the shipment'));
            $this->_redirectReferer();
        }

        if (!$this->_getAndreaniHelper()->isAndreaniShippingCarrier($shipment->getOrder()->getShippingCarrier()))
        {
            Mage::getSingleton('adminhtml/session')->addError($this->_getAndreaniHelper()->__('The carrier isn\'t Andreani'));
            $this->_redirectReferer();
        }

        $tracks = $shipment->getAllTracks();
        if(empty($tracks)){
            Mage::getSingleton('adminhtml/session')->addError($this->_getAndreaniHelper()->__('No tracking numbers were found in the shipment'));
            $this->_redirectReferer();
        }

        $cancelShipmentResponse = array();
        /** @var Mage_Sales_Model_Order_Shipment_Track $track */
        foreach ($tracks as $track) {
            if ($this->_getAndreaniHelper()->isAndreaniCarrierCode($track->getCarrierCode())) {
                $cancelShipmentResponse[$track->getNumber()] =
                    Mage::getSingleton('shipping/config')->getCarrierInstance($track->getCarrierCode())
                        ->cancelShipmentRequest($track->getNumber());
            }
        }
        $allTracksDeleted = true;
        foreach ($cancelShipmentResponse as $trackId => $response) {
            if (!$response->hasErrors() && $response->getCanceledShipment()) {
                $track->delete();
            } else {
                Mage::getSingleton('adminhtml/session')->addError($this->_getAndreaniHelper()->__('Could not cancel Shipment with tracking number %s',$trackId));
                $allTracksDeleted = false;
            }
        }

        if ($allTracksDeleted) {
            $shipment->setShippingLabel('')->save();
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
        if ($this->_getAndreaniHelper()->isAndreaniShippingCarrier($carrier))
        {
            try {
                /** @var $carrier Summa_Andreani_Model_Shipping_Carrier_Abstract */
                $response = $carrier->doShipmentRequest($order,$shipment->getAllItems());

                if($response->hasErrors()){
                    $this->_getAndreaniHelper()->throwException($response->getErrors(),$carrier->getServiceType());
                }
                Mage::getSingleton('adminhtml/session')->addSuccess($this->_getAndreaniHelper()->__('Andreani Shipment with tracking number %s was created successfully',$response->getTrackingNumber()));
                /** @var $helper Summa_Andreani_Helper_Shipments */
                $helper = Mage::helper('summa_andreani/shipments');
                if ($helper->addShippingLabel($shipment,$response)) {
                    Mage::getSingleton('adminhtml/session')->addSuccess($this->_getAndreaniHelper()->__('Successfully recovered constancy link from Andreani for tracking %s',$response->getTrackingNumber()));
                }
                $helper->addTrackingCode($shipment,$response,$carrier);
                $shipment->setSummaAndreaniShipmentStatus(Summa_Andreani_Model_Status::SHIPMENT_NEW)
                    ->save();
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::log($e->getMessage(), null, 'andreani.log');
            }
        } else {
            $this->_getAndreaniHelper()->throwException($this->_getAndreaniHelper()->__('The carrier isn\'t Andreani'));
        }
        $this->_redirectReferer();
    }

    public function generateShipmentsAction()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        /** @var Mage_Sales_Model_Order $order */
        $order = Mage::getModel('sales/order')->load($orderId);
        $result = $this->_getAndreaniHelper()->generateAndreaniShipment($order);
        if (!$result->getResult()) {
            Mage::getSingleton('adminhtml/session')->addError($this->_getAndreaniHelper()->__('Exception threw on Andreani. %s',$result->getErrors()));
        }
        $this->_redirect('*/sales_order/view', array('order_id' => $orderId));
    }

    /**
     * @return Summa_Andreani_Helper_Data
     */
    protected function _getAndreaniHelper()
    {
        return Mage::helper('summa_andreani');
    }
}