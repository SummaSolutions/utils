<?php

class Summa_Andreani_Model_Observer
{
    public function saveShippingMethod(Varien_Event_Observer $observer)
    {
        $request = $observer->getEvent()->getRequest();
        $quote = $observer->getEvent()->getQuote();

        $shippingAddress = $quote->getShippingAddress();
        
        $code = Mage::getModel('summa_andreani/shipping_carrier_storepickup')->getCode();
        
        if ($shippingAddress->getShippingMethod() == ($code . '_' . $code)) {            
            $branchId = $request->getPost('branches_id');
            
            // Set branch ID to the address
            $shippingAddress->setBranchId($branchId);
            
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
                
                $storeName = ucwords(strtolower($branch->getDescription()));
                $shippingAddress->setCompany(Mage::getModel('summa_andreani/shipping_carrier_storepickup')->getConfigData('title') . ' - ' . $storeName );
            }
        }        
    }


    /**
     * @param Varien_Event_Observer $observer
     */
    public function afterInvoiceSave(Varien_Event_Observer $observer)
    {
        /*$order = $observer->getData('data_object')->getOrder();
        $shippingMethod = $order->getShippingMethod();
        if(
            $this->_getHelper()->isAndreaniShippingMethod($shippingMethod) &&
            $this->_getHelper()->isAutoCreateShipmentEnabled()
        ){
            $this->_getHelper()->generateAndreaniShipment($order);
        }*/
    }


    /**
     * @return Summa_Andreani_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('summa_andreani');
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
        /*$shipment = $observer->getShipment();
        if ((bool)$shipment->getAllTracks()) {
            return $this;
        }
        $order = $shipment->getOrder();
        $shippingMethod = $order->getShippingMethod();
        if (Mage::app()->getRequest()->getControllerName() === 'sales_order_shipment' &&
            Mage::app()->getRequest()->getActionName() === "save" &&
            $this->_getHelper()->isAndreaniShippingMethod($shippingMethod)
            )
        {

            // Get Andreani model
            $andreani = Mage::getModel('summa_andreani/shipping_carrier_andreani');

            // Do Shipment Request to Andreani
            $response = $andreani->doShipmentRequest($order, $shipment->getAllItems());
            if($response === false || !$response->ConfirmarCompraResult || !$response->ConfirmarCompraResult->NumeroAndreani){
                throw new Mage_Adminhtml_Exception('Could not create shipment in Andreani');
            }

            $this->_getHelper()->addTrackingComment($order,$shipment,$response->ConfirmarCompraResult->NumeroAndreani);

            if($shippingMethod == 'storepickup_andreani_storepickup_andreani'){ // TODO: research what must be here
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
        return $this;*/
    }
}