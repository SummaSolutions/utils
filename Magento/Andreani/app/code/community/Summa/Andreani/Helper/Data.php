<?php

class Summa_Andreani_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_debuggingEnabled = null;

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
     * Function to know if the shipping carrier instance is one of Andreani Shipping Carriers
     * @param $shippingCarrier
     *
     * @return bool
     */
    public function isAndreaniShippingCarrier($shippingCarrier)
    {
        return $shippingCarrier instanceof Summa_Andreani_Model_Shipping_Carrier_Abstract;
    }

    /**
     * Function to know if the carrier code is one of enabled andreani carriers code
     * @param $carrierCode
     *
     * @return bool
     */
    public function isAndreaniCarrierCode($carrierCode)
    {
        return in_array($carrierCode,$this->getEnabledAndreaniCarrierCodes());
    }

    /**
     * Function to get array with enabled Andreani Methods
     * @return array
     */
    public function getEnabledAndreaniCarrierCodes()
    {
        $carrierCodes = array();

        if (Mage::getSingleton('summa_andreani/shipping_carrier_standard')->isShipmentAvailable()) {
            $carrierCodes[] = Mage::getSingleton('summa_andreani/shipping_carrier_standard')->getCode();
        }

        if (Mage::getSingleton('summa_andreani/shipping_carrier_urgent')->isShipmentAvailable()) {
            $carrierCodes[] = Mage::getSingleton('summa_andreani/shipping_carrier_urgent')->getCode();
        }

        if (Mage::getSingleton('summa_andreani/shipping_carrier_storepickup')->isShipmentAvailable()) {
            $carrierCodes[] = Mage::getSingleton('summa_andreani/shipping_carrier_storepickup')->getCode();
        }

        return $carrierCodes;
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
     * @param Mage_Sales_Model_Order_Shipment $shipmentToAddTracks
     *
     * @return bool
     */
    public function generateAndreaniShipment(Mage_Sales_Model_Order $order, $generateMagentoShipment=true, $shipmentToAddTracks = null)
    {
        /** @var $carrier Summa_Andreani_Model_Shipping_Carrier_Abstract */
        $carrier = $order->getShippingCarrier();

        if (!$this->isAndreaniShippingCarrier($carrier)) {
            return false;
        }
        /** @var $helper Summa_Andreani_Helper_Shipments */
        $helper = Mage::helper('summa_andreani/shipments');

        // Collect data for save Shipment
        $data = array();
        /** @var $item Mage_Sales_Model_Order_Item */
        foreach ($order->getAllItems() as $item) {
            if ($item->getQtyToShip()>0 && !$item->getIsVirtual()
                && !$item->getLockedDoShip())
            {
                $data['items_info'][$item->getId()] = $item->getQtyToShip();
            }
        }

        try {
            $response = $carrier->doShipmentRequest($order);

            if($response->hasErrors()){
                $this->throwException($response->getErrors(),$carrier->getServiceType());
            }
            if ($generateMagentoShipment === true) {

                $data['order_info']['order'] = $order;
                $data['order_info']['carrier'] = $carrier->getCode();
                $data['order_info']['title'] = $this->getConfigData('title',$carrier->getServiceType());
                $data['order_info']['tracking_number'] = $response->getTrackingNumber();

                $shipment_id = $helper->saveShipment($data);
                $helper->addShippingLabel($shipment_id,$response);

            } elseif ($shipmentToAddTracks !== null) { // support for generation of shipments from Magento Admin
                $helper->addShippingLabel($shipmentToAddTracks,$response);
                $helper->addTrackingCode($shipmentToAddTracks,$response,$carrier);
            }
            return true;
        } catch (Exception $e) {
            Mage::getSingleton('core/session')->addError($e->getMessage());
            $this->debugging($e->getMessage());
            return false;
        }
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
     * Return Auto Create Shipment On Invoice creation Enabled true or false
     *
     * @return bool
     */
    public function isAutoCreateShippingOnShipmentEnabled()
    {
        return $this->getConfigData('autocreate_shipping_on_shipment_create');
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
        return Mage::getStoreConfig('carriers/andreani' . ucfirst($service) . '/' . $path);
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
            $this->_debuggingEnabled = $this->getConfigData('debug',$service);
        } else {
            $this->_debuggingEnabled = true;
        }

        if ($this->_debuggingEnabled) {
            Mage::log($this->__($toLog), null, 'andreani.log',true);
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
        if ($service !== 'global' && $this->getConfigData('debug',$service)) {
            $info = $reason;
        }
        throw new Mage_Adminhtml_Exception(
            $this->__(
                'Exception throwed on Andreani. %s',
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
     * Function to get is Custom for $config based on $service
     * @param $config
     * @param $service
     *
     * @return mixed
     */
    public function isCustomConfigEnabled($config,$service)
    {
        return ($this->getConfigData('is_custom_'.$config,$service)) ? $service : 'global' ;
    }

    /**
     * Function to get Username
     * @param string $service
     *
     * @return mixed
     */
    public function getUsername($service = 'global')
    {
        return $this->getConfigData('username',$this->isCustomConfigEnabled('username',$service));
    }

    /**
     * Function to get Password
     * @param string $service
     *
     * @return mixed
     */
    public function getPassword($service = 'global')
    {
        return $this->getConfigData('password',$this->isCustomConfigEnabled('password',$service));
    }

    /**
     * Function to get Client Number
     * @param string $service
     *
     * @return mixed
     */
    public function getClientNumber($service = 'global')
    {
        return $this->getConfigData('client_number',$this->isCustomConfigEnabled('client_number',$service));
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
     * Function to get Free method text
     * @param string $service
     *
     * @return mixed
     */
    public function getFreeMethodText($service = 'global')
    {
        return $this->getConfigData('free_method_text',$service);
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
     * Function to get Singleton of WsseHeader
     *
     * @param $username
     * @param $password
     *
     * @return Summa_Andreani_Model_Api_Soap_Header
     */
    public function getWsseHeader($username,$password)
    {
        return Mage::getSingleton('summa_andreani/api_soap_header', array('username' => $username, 'password' => $password));
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

    /**
     * Function to Calculate Weight
     * @param Mage_Catalog_Model_Product $product
     *
     * @return float
     */
    public function calculateWeight($product)
    {
        $height = 0;
        $width = 0;
        $length = 0;
        if ($product->getTypeId() === "simple") {
            $height = ($product->getData($this->getConfigData('attribute_height')))?
                $product->getData($this->getConfigData('attribute_height')):
                Mage::getSingleton('catalog/resource_product')->getAttributeRawValue(
                    $product->getId(),
                    $this->getConfigData('attribute_height'),
                    Mage::app()->getStore()
                );
            $height = ($height)?$height:0;

            $width = ($product->getData($this->getConfigData('attribute_width')))?
                $product->getData($this->getConfigData('attribute_width')):
                Mage::getSingleton('catalog/resource_product')->getAttributeRawValue(
                    $product->getId(),
                    $this->getConfigData('attribute_width'),
                    Mage::app()->getStore()
                );
            $width = ($width)?$width:0;

            $length = ($product->getData($this->getConfigData('attribute_length')))?
                $product->getData($this->getConfigData('attribute_length')):
                Mage::getSingleton('catalog/resource_product')->getAttributeRawValue(
                    $product->getId(),
                    $this->getConfigData('attribute_length'),
                    Mage::app()->getStore()
                );
            $length = ($length)?$length:0;
        }
        return ($width * $height * $length) * 350 / 1000;
    }

    /**
     * @return Summa_Andreani_Model_Status
     */
    public function getStatusSingleton()
    {
        return Mage::getSingleton('summa_andreani/status');
    }

    /**
     * Function to add comment with constancy link
     * @param Mage_Sales_Model_Order $order
     * @param Mage_Sales_Model_Order_Shipment $shipment
     * @param string $trackingNumber
     *
     * @return bool
     * @deprecated
     */
    public function addConstancyComment($order, $shipment, $trackingNumber)
    {
        /* Get Andreani model */
        /** @var $andreani Summa_Andreani_Model_Shipping_Carrier_Abstract */
        $andreani = $order->getShippingCarrier();

        /* Get Link to Andreani PDF */
        $constancyResponse = $andreani->getLinkConstancy($trackingNumber);
        if (isset($constancyResponse->ImprimirConstanciaResult)) {

            $comment = $this->getCommentStringForConstancy($constancyResponse->ImprimirConstanciaResult->ResultadoImprimirConstancia->PdfLinkFile);

            /* Save Link in History */
            if ($shipment->getId()) {
                Mage::getModel('sales/order_shipment_api')->addComment($shipment->getIncrementId(), $comment);
            }

            return true;
        }
        return false;
    }

    /**
     * Function to generate comment string for constancy
     * @param $constancyURL
     *
     * @return string
     *
     * @deprecated
     */
    public function getCommentStringForConstancy($constancyURL)
    {
        return $this->__('PDF Constancy: <a href="%s" target="_blank"> Click here </a>',$constancyURL);
    }
}
