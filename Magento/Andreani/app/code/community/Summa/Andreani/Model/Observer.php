<?php

class Summa_Andreani_Model_Observer
{
    public function saveShippingMethod(Varien_Event_Observer $observer)
    {
        $request = $observer->getEvent()->getRequest();
        $quote = $observer->getEvent()->getQuote();

        $shippingAddress = $quote->getShippingAddress();
        
        $code = Mage::getModel('summa_andreani/shipping_sucursal')->getCode();
        
        if ($shippingAddress->getShippingMethod() == ($code . '_' . $code)) {            
            $branchId = $request->getPost('branches_id');
            
            // Set branch ID to the address
            $shippingAddress->setBranchId($branchId);
            
            $store = Mage::getModel('summa_andreani/sucursal')->load($branchId, 'sucursal_id');
            if ($store) {
                $shippingAddress->setStreet($store->getDireccion());
                $shippingAddress->setCity(ucwords(strtolower($store->getLocalidad())));
                
                $region = Mage::getModel('directory/region')->load($store->getRegionId());
                if ($region) {
                    $shippingAddress->setRegion($region->getDefaultName());
                }
                
                $shippingAddress->setRegionId($store->getRegionId());
                $shippingAddress->setPostcode($store->getCodigoPostal());
                
                $phone = $store->getTelefono1();
                if (!$phone) {
                    $phone = 'N/A';
                }
                $shippingAddress->setTelephone($phone);
                
                $storeName = ucwords(strtolower($store->getDescripcion()));
                $shippingAddress->setCompany(Mage::getModel('summa_andreani/shipping_sucursal')->getConfigData('title') . ' - ' . $storeName );
            }
        }        
    }


    /**
     * @param Varien_Event_Observer $observer
     */
    public function afterInvoiceSave(Varien_Event_Observer $observer)
    {
        $order = $observer->getData('data_object')->getOrder();
        $shippingMethod = $order->getShippingMethod();
        if(
            (
                $shippingMethod == 'storepickup_andreani_storepickup_andreani' ||
                strpos($shippingMethod,'matrixrate') !== false
            ) &&
            $this->_getHelper()->isAutoCreateShipmentEnabled()
        ){
            $this->_getHelper()->generateAndreaniShipment($order);
        }
    }


    /**
     * @return Summa_Andreani_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('summa_andreani');
    }

    public function shipmentSaveBefore(Varien_Event_Observer $observer)
    {
        $shipment = $observer->getShipment();
        if ((bool)$shipment->getAllTracks()) {
            return $this;
        }
        $order = $shipment->getOrder();
        $shippingMethod = $order->getShippingMethod();
        if (Mage::app()->getRequest()->getControllerName() === 'sales_order_shipment' &&
            Mage::app()->getRequest()->getActionName() === "save" &&
            (strpos($shippingMethod,'matrixrate') !== false || $shippingMethod == 'storepickup_andreani_storepickup_andreani')
            )
        {

            /* Get Andreani model */
            $andreani = Mage::getModel('summa_andreani/shipping_carrier_andreani');

            /* Do Shipment Request to Andreani */
            $response = $andreani->doShipmentRequest($order, $shipment->getAllItems());
            if($response === false || !$response->ConfirmarCompraResult || !$response->ConfirmarCompraResult->NumeroAndreani){
                throw new Mage_Adminhtml_Exception('Could not create shipment in Andreani');
            }

            $this->_getHelper()->addTrackingComment($order,$shipment,$response->ConfirmarCompraResult->NumeroAndreani);

            if($shippingMethod == 'storepickup_andreani_storepickup_andreani'){
                $carrierCode = 'storepickup_andreani';
            } else {
                $carrierCode = 'matrixrate';
            }

            $track = Mage::getModel('sales/order_shipment_track');
            $track->setCarrierCode($carrierCode)
                ->setTitle("Recibo " . $response->ConfirmarCompraResult->Recibo)
                ->setNumber($response->ConfirmarCompraResult->NumeroAndreani);
            $shipment->addTrack($track);
        }
        return $this;
    }
}