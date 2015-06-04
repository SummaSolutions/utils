<?php

class Summa_Andreani_Model_Observer
{
    /**
     * Function to set Shipping Address of Andreani Branch when
     * customer select Andreani Storepickup shipment
     * @param Varien_Event_Observer $observer
     */
    public function saveShippingMethod(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Quote $quote */
        $quote = $observer->getEvent()->getQuote();
        /** @var Mage_Sales_Model_Quote_Address $shippingAddress */
        $shippingAddress = $quote->getShippingAddress();

        $code = Mage::getModel('summa_andreani/shipping_carrier_storepickup')->getCode();

        $quoteCarrierCode = explode('_',$shippingAddress->getShippingMethod());
        if (current($quoteCarrierCode) === $code) {
            $branchId = end($quoteCarrierCode);
            
            // Set branch ID to the address
            $shippingAddress->setAndreaniBranchId($branchId);
            
            $branch = Mage::getModel('summa_andreani/branch')->load($branchId, 'branch_id');
            if ($branch) {
                $shippingAddress->setStreet($branch->getAddress());
                $shippingAddress->setCity(ucwords(strtolower($branch->getCity())));
                
                $region = Mage::getModel('directory/region')->load($branch->getRegionId());
                if ($region) {
                    $shippingAddress->setRegion($region->getDefaultName());
                }
                
                $shippingAddress->setRegionId($branch->getRegionId());
                $shippingAddress->setPostcode($branch->getPostalCode());
                
                $phone = $branch->getPhone1();
                if (!$phone) {
                    $phone = 'N/A';
                }
                $shippingAddress->setTelephone($phone);
            }
        }        
    }

    /**
     * @param Varien_Event_Observer $observer
     */
    public function afterInvoiceSave(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getData('data_object')->getOrder();
        $shippingCarrier = $order->getShippingCarrier();
        if(
            $this->_getHelper()->isAndreaniShippingCarrier($shippingCarrier) &&
            $this->_getHelper()->isAutoCreateShipmentOnInvoiceEnabled()
        ){
            $result = $this->_getHelper()->generateAndreaniShipment($order);
            if (!$result->getResult()) {
                Mage::getSingleton('adminhtml/session')->addError($this->_getAndreaniHelper()->__('Exception threw on Andreani. %s',$result->getErrors()));
            }
        }
    }

    /**
     * @return Summa_Andreani_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('summa_andreani');
    }

    /**
     * @return Summa_Andreani_Helper_Shipments
     */
    protected function _getShipmentHelper()
    {
        return Mage::helper('summa_andreani/shipments');
    }

    /**
     * Function to generate Shipment Request to andreani before save shipment on Admin
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     * @throws Mage_Adminhtml_Exception
     */
    public function shipmentSaveBefore(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order_Shipment $shipment */
        $shipment = $observer->getShipment();
        if ((bool)$shipment->getAllTracks()) {
            return $this;
        }
        $order = $shipment->getOrder();
        /** @var Summa_Andreani_Model_Shipping_Carrier_Abstract $carrier */
        $carrier = $order->getShippingCarrier();
        if (
            Mage::app()->getRequest()->getControllerName() === 'sales_order_shipment' &&
            Mage::app()->getRequest()->getActionName() === "save" &&
            $this->_getHelper()->isAndreaniShippingCarrier($carrier) &&
            $this->_getHelper()->isAutoCreateShippingOnShipmentEnabled()
            )
        {
            // Do Shipment Request to Andreani
            $response = $carrier->doShipmentRequest($order, $shipment->getAllItems());
            if($response->hasErrors()){
                $this->_getHelper()->throwException($response->getErrors(),$carrier->getServiceType());
            }
            $this->_getShipmentHelper()->addTrackingCode($shipment,$response,$carrier);
            $this->_getShipmentHelper()->addShippingLabel($shipment,$response);
            $shipment->setSummaAndreaniShipmentStatus(Summa_Andreani_Model_Status::SHIPMENT_NEW)
                ->save();
        }
        return $this;
    }

    /**
     * Observer on before product save to calculate product weight
     * @param Varien_Event_Observer $observer
     */
    public function beforeProductSave(Varien_Event_Observer $observer)
    {
        /** @var Mage_Catalog_Model_Product $product */
        $product = $observer->getProduct();
        $attrCode = $this->_getHelper()->getConfigData('attribute_weight');
        $product->setData($attrCode,$this->_getHelper()->calculateWeight($product));
    }

    /**
     * Observer on before track delete to call andreani and cancel shipment request
     * @param Varien_Event_Observer $observer
     */
    public function beforeTrackDelete(Varien_Event_Observer $observer)
    {
        /** @var $track Mage_Sales_Model_Order_Shipment_Track */
        $track = $observer->getTrack();
        $carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($track->getCarrierCode());

        if ($carrier instanceof Summa_Andreani_Model_Shipping_Carrier_Abstract)
        {
            try {
                $response = $carrier->cancelShipmentRequest($track->getNumber());
                if (!$response->hasErrors() && $response->getCanceledShipment()) {
                    Mage::getSingleton('adminhtml/session')->addSuccess($this->_getHelper()->__('Shipment with tracking number %s was cancelled successfully',$track->getNumber()));
                } else {
                    Mage::getSingleton('adminhtml/session')->addError($this->_getHelper()->__('Could not cancel Shipment with tracking number %s',$track->getNumber()));
                }
            }catch(Exception $e){
                Mage::getSingleton('adminhtml/session')->addError($this->_getHelper()->__('Could not cancel shipment in Andreani'));
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
    }

    /**
     * Observer on Shipment save after GLOBAL
     * At this moment, this observer set Order status based-on Shipment Andreani Status
     * @param Varien_Event_Observer $observer
     *
     * @throws Exception
     */
    public function shipmentGlobalSaveAfter(Varien_Event_Observer $observer)
    {
        $shipment = $observer->getShipment();
        /** @var Mage_Sales_Model_Order $order */
        $order = $shipment->getOrder();
        $saveOrder = false;
        switch ($shipment->getSummaAndreaniShipmentStatus()) {
            case Summa_Andreani_Model_Status::SHIPMENT_NEW:
                if ($order->getStatus() !== Summa_Andreani_Model_Status::ORDER_STATUS_NEW) {
                    $order->setStatus(Summa_Andreani_Model_Status::ORDER_STATUS_NEW);
                    $saveOrder = true;
                }
                break;
            case Summa_Andreani_Model_Status::SHIPMENT_PROCESSING:
                if ($order->getStatus() !== Summa_Andreani_Model_Status::ORDER_STATUS_PROCESSING) {
                    $order->setStatus($this->_getHelper()->getConfigData('andreani_shipping_others'));
                    $saveOrder = true;
                }
                break;
            case Summa_Andreani_Model_Status::SHIPMENT_COMPLETED:
                if ($order->getStatus() !== Summa_Andreani_Model_Status::ORDER_STATUS_COMPLETED) {
                    $order->setStatus($this->_getHelper()->getConfigData('andreani_shipping_completed'));
                    $saveOrder = true;
                }
                break;
            case Summa_Andreani_Model_Status::SHIPMENT_PENDING:
                if ($order->getStatus() !== Summa_Andreani_Model_Status::ORDER_STATUS_PENDING) {
                    $order->setStatus($this->_getHelper()->getConfigData('andreani_shipping_failed'));
                    $saveOrder = true;
                }
                break;
        }

        if ($saveOrder) {
            $order->save();
        }
    }

    /**
     * Function to set Summa Andreani Insurance on Invoice Save After
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function invoiceSaveAfter(Varien_Event_Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        if ($invoice->getBaseSummaAndreaniInsuranceAmount()) {
            $order = $invoice->getOrder();
            $order->setSummaAndreaniInsuranceAmountInvoiced($order->getSummaAndreaniInsuranceAmountInvoiced() + $invoice->getSummaAndreaniInsuranceAmount());
            $order->setBaseSummaAndreaniInsuranceAmountInvoiced($order->getBaseSummaAndreaniInsuranceAmountInvoiced() + $invoice->getBaseSummaAndreaniInsuranceAmount());
        }
        return $this;
    }

    /**
     * Function to set Summa Andreani Insurance on Creditmemo Save After
     * @param Varien_Event_Observer $observer
     *
     * @return $this
     */
    public function creditmemoSaveAfter(Varien_Event_Observer $observer)
    {
        /* @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
        $creditmemo = $observer->getEvent()->getCreditmemo();
        if ($creditmemo->getSummaAndreaniInsuranceAmount()) {
            $order = $creditmemo->getOrder();
            $order->setSummaAndreaniInsuranceAmountRefunded($order->getSummaAndreaniInsuranceAmountRefunded() + $creditmemo->getSummaAndreaniInsuranceAmount());
            $order->setBaseSummaAndreaniInsuranceAmountRefunded($order->getBaseSummaAndreaniInsuranceAmountRefunded() + $creditmemo->getBaseSummaAndreaniInsuranceAmount());
        }
        return $this;
    }

    /**
     * Function to set Summa Andreani Insurance on Update Paypal Total
     * @param $evt
     */
    public function updatePaypalTotal($evt){
        $cart = $evt->getPaypalCart();
        $cart->updateTotal(Mage_Paypal_Model_Cart::TOTAL_SUBTOTAL,$cart->getSalesEntity()->getSummaAndreaniInsuranceAmount());
    }

    /**
     * Function to set Number, Floor, Apartment and Dni on do shipment Request
     * @param Varien_Event_Observer $observer
     */
    public function beforeDoShipmentRequestAddDataExtra(Varien_Event_Observer $observer)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $observer->getOrder();

        $address = $order->getShippingAddress();

        $number = $address->getNumber();
        $floor = $address->getFloor();
        $apartment = $address->getApartment();
        $DNI_number = $address->getDni();

        $shipmentInfo = $observer->getShipmentInfo();

        $shipmentInfo['compra']['Numero'] = $number;
        $shipmentInfo['compra']['Departamento'] = $apartment;
        $shipmentInfo['compra']['Piso'] = $floor;
        $shipmentInfo['compra']['NumeroDocumento'] = $DNI_number;
    }
}