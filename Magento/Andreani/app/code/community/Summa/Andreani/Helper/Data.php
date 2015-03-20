<?php

class Summa_Andreani_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_debuggingEnabled = null;

    public function formatBranchInfo(Summa_Andreani_Model_Sucursal $branch)
    {
        return ucwords(strtolower($branch->getDescripcion() . ' - ' . $branch->getDireccion()));
    }

    public function getSucursalesJson()
    {
        $sucursales = Mage::getModel('summa_andreani/sucursal')->getBranches();

        $stores = array();

        foreach ($sucursales as $sucursal) {
            $stores[$sucursal->getRegionId()][$sucursal->getSucursalId()] = array(
                'code' => $sucursal->getSucursalId(),
                'name' => $this->formatBranchInfo($sucursal)
            );
        }
        $json = Mage::helper('core')->jsonEncode($stores);

        return $json;
    }

    public function getRegionIds()
    {
        $sucursales = Mage::getModel('summa_andreani/sucursal')->getBranches();
        
        $ids = array();

        foreach ($sucursales as $sucursal) {
            $ids[] = $sucursal->getRegionId();
        }

        return $ids;
    }

    public function splitDate($dateTime)
    {
        if(empty($dateTime)){
            return array('', '');
        }


        $locale = Mage::app()->getLocale()->getLocaleCode();
        $date = new Zend_Date($dateTime, null, $locale);
        $return = array(
            $date->toString('y/MM/dd'),
            $date->toString('HH:mm:ss')
        );

        return $return;
    }

    /**
     * Function to generate Andreani Shipment and Magento Shipment natively
     * If you want don't create Magento Shipment (ex. if you are generating the
     * shipment using Magento Admin) you can send $generateMagentoShipment false
     * but you must pass $shipmentToAddTracks in order to don't lose information
     * about response from Andreani.
     *
     * @param Mage_Sales_Model_Order $order
     * @param bool $generateMagentoShipment
     * @param null $shipmentToAddTracks
     */
    public function generateAndreaniShipment(Mage_Sales_Model_Order $order, $generateMagentoShipment=true, $shipmentToAddTracks = null)
    {
        /* Get Andreani model */
        /** @var  $andreani Summa_Andreani_Model_Shipping_Carrier_Abstract */
        $andreani = Mage::getSingleton('summa_andreani/shipping_carrier_andreani');

        /* Get Helper of Shipments */
        /** @var $helper Summa_Andreani_Helper_Shipments */
        $helper = Mage::helper('summa_andreani/shipments');

        /* Collect data for save Shipment */
        $data = array();
        /** @var $item Mage_Sales_Model_Order_Item */
        foreach ($order->getAllItems() as $item) {
            if ($item->getQtyToShip()>0 && !$item->getIsVirtual()
                && !$item->getLockedDoShip())
            {
                if (!isset($data['items_info'][$item->getId()])) {
                    $data['items_info'][$item->getId()] = 1;
                } else {
                    $data['items_info'][$item->getId()] += 1;
                }
            }
        }

        /* Save Shipment after call Andreani */
        try {
            $response = $andreani->doShipmentRequest($order);

            if($response === false || !$response->ConfirmarCompraResult || !$response->ConfirmarCompraResult->NumeroAndreani){
                throw new Mage_Adminhtml_Exception('Could not create shipment in Andreani');
            }
            if ($generateMagentoShipment === true) {

                $data['order_info']['order'] = $order;
                $data['order_info']['carrier'] = 'matrixrate';
                $data['order_info']['date'] = "Recibo " . $response->ConfirmarCompraResult->Recibo;
                $data['order_info']['tracking_number'] = $response->ConfirmarCompraResult->NumeroAndreani;

                $shipment_id = $helper->saveShipment($data);
                $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipment_id);
                $this->addTrackingComment($order, $shipment, $response->ConfirmarCompraResult->NumeroAndreani);

            } elseif ($shipmentToAddTracks !== null) { // support for generation of shipments from Magento Admin

                $track = Mage::getModel('sales/order_shipment_track');
                $track->setCarrierCode('matrixrate')
                    ->setTitle("Recibo " . $response->ConfirmarCompraResult->Recibo)
                    ->setNumber($response->ConfirmarCompraResult->NumeroAndreani);
                $shipmentToAddTracks->addTrack($track);

            }
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            Mage::log($e->getMessage(), null, 'Summa_Andreani_Model_Observer.log');
        }
    }

    public function addTrackingComment($order, $shipment, $trackingNumber)
    {
        /* Get Andreani model */
        /** @var  $andreani Summa_Andreani_Model_Shipping_Carrier_Abstract */
        $andreani = Mage::getModel('summa_andreani/shipping_carrier_andreani');

        /* Get Link to Andreani PDF */
        $linkConstancia = $andreani->getLinkConstancia($trackingNumber,$order);
        if (isset($linkConstancia->ImprimirConstanciaResult)) {
            /* Create Comment with Link to PDF */
            $comment = 'Constancia PDF: |SEPARATOR| <a href="' . $linkConstancia->ImprimirConstanciaResult->ResultadoImprimirConstancia->PdfLinkFile . '" target="_blank"> Click Aqui </a>';

            /* Save Link in History */
            if ($shipment->getId()) {
                Mage::getModel('sales/order_shipment_api')->addComment($shipment->getIncrementId(), $comment);
            }

            //Send Email
            /*
            $sender = array(
                'name'  => Mage::getStoreConfig('trans_email/ident_general/name'),
                'email' => Mage::getStoreConfig('trans_email/ident_general/email')
            );

            $templateId = 5; // ¿?

            $vars = array(
                'link_pdf' => $linkConstancia->ImprimirConstanciaResult->ResultadoImprimirConstancia->PdfLinkFile,
                'order'    => $order,
                'payment'  => ($order->getPayment()->getMethod() == 'mpexpress') ? "Mercado Pago" : "Efectivo",// ¿?
                'shipment' => $shipment
            );

            $email = Mage::getModel('core/email_template');
            $email->emulateDesign(1);
            $email->sendTransactional($templateId, $sender, Mage::getStoreConfig('trans_email/ident_sales/email'), Mage::getStoreConfig('trans_email/ident_sales/name'), $vars, Mage::app()->getStore()->getStoreId());
            */
        }
    }

    /**
     * Return Auto Create Shipment Enabled true or false
     *
     * @return bool
     */
    public function isAutoCreateShipmentEnabled()
    {
        return $this->getConfigData('autocreate_shipping');
    }

    /**
     * Returns config Data
     *
     * @param        $path
     * @param string $service
     *
     * @return mixed
     */
    public function getConfigData($path,$service = 'global')
    {
        return Mage::getStoreConfig('carriers/' . $service . '_andreani/' . $path);
    }

    /**
     * Function to log information, returns true or false depends if can log or not
     *
     * @param        $toLog
     * @param string $service
     *
     * @return bool
     */
    public function debugging($toLog,$service = 'global')
    {
        if (is_null($this->_debuggingEnabled)) {
            $this->_debuggingEnabled = $this->getConfigData('debug_mode',$service);
        }

        if ($this->_debuggingEnabled) {
            Mage::log($toLog, null, 'andreani.log',true);
        }

        return $this->_debuggingEnabled;
    }

    /**
     * Function to throwException
     *
     * @param $reason
     * @param $service
     *
     * @throws Mage_Adminhtml_Exception
     */
    public function throwException($reason,$service)
    {
        $info = '';
        if ($this->getConfigData('debug_mode',$service)) {
            $info = $reason;
        }
        throw new Mage_Adminhtml_Exception(
            $this->__(
                'Could not create shipment in Andreani. %s',
                $this->__($info)
            )
        );
    }

    /**
     * Function to get Active Config for
     * @param $service
     *
     * @return bool
     */
    public function isEnabled($service)
    {
        return $this->getConfigData('active',$service);
    }
}
