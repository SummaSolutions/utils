<?php

class Summa_Andreani_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_debuggingEnabled = null;

    public function formatBranchInfo(Summa_Andreani_Model_Sucursal $branch)
    {
        return ucwords(strtolower($branch->getDescription() . ' - ' . $branch->getAddress()));
    }

    public function getBranchesJson()
    {
        $branches = Mage::getModel('summa_andreani/branch')->getBranches();

        $stores = array();

        foreach ($branches as $branch) {
            $stores[$branch->getRegionId()][$branch->getBranchId()] = array(
                'code' => $branch->getBranchId(),
                'name' => $this->formatBranchInfo($branch)
            );
        }
        $json = Mage::helper('core')->jsonEncode($stores);

        return $json;
    }

    public function getRegionIds()
    {
        $branches = Mage::getModel('summa_andreani/branch')->getBranches();
        
        $ids = array();

        foreach ($branches as $branch) {
            $ids[] = $branch->getRegionId();
        }

        return $ids;
    }

    /**
     * Function to split date from web service Andreani on TrackingCodes.
     * @param $dateTime
     *
     * @return array
     */
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
     * Function to show button on admin shipment view to call Andreani Web service and get Constancy Link
     * @param $shipment Mage_Sales_Model_Order_Shipment
     * @return bool
     */
    public function canGenerateConstancy($shipment)
    {
        $shippingMethod = $shipment->getOrder()->getShippingMethod();
        if ($this->isAndreaniShippingMethod($shippingMethod)) {
            return false;
        }

        $tracks = $shipment->getAllTracks();
        if(empty($tracks)){
            return false;
        }
        $found = false;
        $comments = $shipment->getCommentsCollection();
        /** @var $comment Mage_Sales_Model_Resource_Order_Shipment_Comment */
        foreach ($comments as $comment)
        {
            if (strpos($comment->getComment(),'Constancia PDF: |SEPARATOR| <a href') !== false) {
                $found = true;
                break;
            }
        }
        return !$found;
    }

    /**
     * Function to show button on admin shipment view to call Andreani Web service and get Constancy Link
     * @param $shipment Mage_Sales_Model_Order_Shipment
     * @return bool
     */
    public function canCancelShipment($shipment)
    {
        $shippingMethod = $shipment->getOrder()->getShippingMethod();
        if ($this->isAndreaniShippingMethod($shippingMethod)) {
            return false;
        }
        if ($shipment->getOrder()->canShip()) {
            return false;
        }
        if ($shipment->getShipmentStatus() == "Shipped") { // TODO: Change to status Shipped
            return true;
        }
        return false;
    }

    /**
     * Function to know if the shipping Method is one of enabled andreani shipping methods
     * @param $shippingMethod
     *
     * @return bool
     */
    public function isAndreaniShippingMethod($shippingMethod)
    {
        return in_array($shippingMethod,$this->getEnabledAndreaniMethods());
    }

    /**
     * Function to get array with enabled Andreani Methods
     * @return array
     */
    public function getEnabledAndreaniMethods()
    {
        $methods = array();

        if (Mage::getSingleton('summa_andreani/shipping_carrier_standard')->isShipmentAvailable()) {
            $methods[] = implode('_',array(Mage::getSingleton('summa_andreani/shipping_carrier_standard')->getCode(),Mage::getSingleton('summa_andreani/shipping_carrier_standard')->getCode()));
        }

        if (Mage::getSingleton('summa_andreani/shipping_carrier_urgent')->isShipmentAvailable()) {
            $methods[] = implode('_',array(Mage::getSingleton('summa_andreani/shipping_carrier_urgent')->getCode(),Mage::getSingleton('summa_andreani/shipping_carrier_urgent')->getCode()));
        }

        if (Mage::getSingleton('summa_andreani/shipping_carrier_storepickup')->isShipmentAvailable()) {
            $methods[] = implode('_',array(Mage::getSingleton('summa_andreani/shipping_carrier_storepickup')->getCode(),Mage::getSingleton('summa_andreani/shipping_carrier_storepickup')->getCode()));
        }

        return $methods;
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
        $andreani = $order->getShippingCarrier();

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
                $data['items_info'][$item->getId()] = $item->getQtyInvoiced();
            }
        }

        /* Save Shipment after call Andreani */
        try {
            $response = $andreani->doShipmentRequest($order);

            if($response === false || !$response->ConfirmarCompraResult || !$response->ConfirmarCompraResult->NumeroAndreani){
                $this->throwException();
            }
            if ($generateMagentoShipment === true) {

                $data['order_info']['order'] = $order;
                $data['order_info']['carrier'] = $andreani->getCode();
                $data['order_info']['date'] = $this->__('Receive') . ' '  . $response->ConfirmarCompraResult->Recibo;
                $data['order_info']['tracking_number'] = $response->ConfirmarCompraResult->NumeroAndreani;

                $shipment_id = $helper->saveShipment($data);
                $shipment = Mage::getModel('sales/order_shipment')->loadByIncrementId($shipment_id);
                $this->addTrackingComment($order, $shipment, $response->ConfirmarCompraResult->NumeroAndreani);

            } elseif ($shipmentToAddTracks !== null) { // support for generation of shipments from Magento Admin

                $track = Mage::getModel('sales/order_shipment_track');
                $track->setCarrierCode($andreani->getCode())
                    ->setTitle($this->__('Receive') . ' ' . $response->ConfirmarCompraResult->Recibo)
                    ->setNumber($response->ConfirmarCompraResult->NumeroAndreani);
                $shipmentToAddTracks->addTrack($track);

            }
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            $this->debugging($e->getMessage());
        }
    }

    /**
     * Function to add tracking comment with constancy link
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param string $trackingNumber
     *
     * @return bool
     */
    public function addTrackingComment($order, $shipment, $trackingNumber)
    {
        /* Get Andreani model */
        /** @var $andreani Summa_Andreani_Model_Shipping_Carrier_Abstract */
        $andreani = $order->getShippingCarrier();

        /* Get Link to Andreani PDF */
        $linkConstancia = $andreani->getLinkConstancy($trackingNumber);
        if (isset($linkConstancia->ImprimirConstanciaResult)) {
            /* Create Comment with Link to PDF */ // TODO FOUND SOMETHING BETTER
            $comment = 'Constancia PDF: |SEPARATOR| <a href="' . $linkConstancia->ImprimirConstanciaResult->ResultadoImprimirConstancia->PdfLinkFile . '" target="_blank"> Click Aqui </a>';

            /* Save Link in History */
            if ($shipment->getId()) {
                Mage::getModel('sales/order_shipment_api')->addComment($shipment->getIncrementId(), $comment);
            }
            // TODO CONFIGURATION FOR ALL
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
                'payment'  => $order->getPayment()->getMethodInstance()->getTitle(),
                'shipment' => $shipment
            );

            $email = Mage::getModel('core/email_template');
            $email->emulateDesign(1);
            $email->sendTransactional($templateId, $sender, Mage::getStoreConfig('trans_email/ident_sales/email'), Mage::getStoreConfig('trans_email/ident_sales/name'), $vars, Mage::app()->getStore()->getStoreId());
            */
            return true;
        }
        return false;
    }

    /**
     * Return Auto Create Shipment On Invoice creation Enabled true or false
     *
     * @return bool
     */
    public function isAutoCreateShipmentOnInvoiceEnabled()
    {
        return $this->getConfigData('autocreate_shipping_on_invoice_create');
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
        return Mage::getStoreConfig('carriers/andreani_' . $service . '/' . $path);
    }

    /**
     * Returns config Data from MatrixRates
     *
     * @param        $path
     * @param string $service
     *
     * @return mixed
     */
    public function getConfigDataFromMatrixRates($path)
    {
        return Mage::getStoreConfig('carriers/matrixrate/' . $path);
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
        if ($service != 'global') {
            $this->_debuggingEnabled = $this->getConfigData('debug_mode',$service);
        } else {
            $this->_debuggingEnabled = true;
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
    public function throwException($reason = '',$service = 'global')
    {
        $info = '';
        if ($service !== 'global' && $this->getConfigData('debug_mode',$service)) {
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

    /**
     * Function to get Username
     * @param string $service
     *
     * @return mixed
     */
    public function getUsername($service = 'global')
    {
        return $this->getConfigData('username',$service);
    }

    /**
     * Function to get Password
     * @param string $service
     *
     * @return mixed
     */
    public function getPassword($service = 'global')
    {
        return $this->getConfigData('password',$service);
    }

    /**
     * Function to get Contract
     * @param string $service
     *
     * @return mixed
     */
    public function getContract($service = 'global')
    {
        return $this->getConfigData('contract',$service);
    }

    /**
     * Function to get Client Number
     * @param string $service
     *
     * @return mixed
     */
    public function getClientNumber($service = 'global')
    {
        return $this->getConfigData('client_number',$service);
    }

    /**
     * Function to get SOAP Options
     * @return array
     */
    public function getSoapOptions()
    {
        return array(
            'soap_version' => SOAP_1_2,
            'exceptions' => true,
            'trace' => 1,
            'wdsl_local_copy' => true
        );
    }

    /**
     * Function to Calculate Insurance
     * @param $subtotal
     *
     * @return float
     */
    public function calculateInsurance($subtotal)
    {
        return $subtotal * $this->getConfigData('insurance') / 100;
    }

    /**
     * Function to Calculate IVA
     * @param $ratePrice
     *
     * @return float
     */
    public function calculateIVA($ratePrice)
    {
        return $ratePrice * $this->getConfigData('iva_percentage') / 100;
    }
}
