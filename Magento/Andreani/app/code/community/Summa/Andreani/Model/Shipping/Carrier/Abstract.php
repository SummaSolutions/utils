<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        19/03/15
 * Time:        09:08
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */

abstract class Summa_Andreani_Model_Shipping_Carrier_Abstract
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    protected $_code = 'andreani_abstract';
    protected $_service = null;
    protected $_serviceType = 'global';
    protected $_result;
    protected $_limitWeight = null;
    protected $_shippingTypeForMatrixrates = 'Abstract';


    public function isTrackingAvailable()
    {
        return true;
    }

    public function isShipmentAvailable()
    {
        return $this->_getHelper()->isEnabled($this->getServiceType());
    }

    /**
     * Function to return array with allowed Methods
     *
     * This model will be used like abstract for the real carriers
     * then this method will be used for get array of all andreani
     * services enabled
     *
     * @return array
     */
    public function getAllowedMethods()
    {
        return array();
    }

    /**
     * Function to return Rates availables for the request
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return bool|Mage_Shipping_Model_Rate_Result|null
     */
    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if (!$this->isShipmentAvailable())
        {
            return false;
        }

        if (false) { // TODO: Change for config use web service or matrixrates
            return $this->_collectRatesByWebService($request);
        } else {
            return $this->_collectRatesByMatrixRates($request);
        }
    }

    /**
     * Do request to shipment
     *
     * @param Mage_Shipping_Model_Shipment_Request $request
     * @return Varien_Object
     */
    public function requestToShipment(Mage_Shipping_Model_Shipment_Request $request)
    {
        $this->_getHelper()->debugging('requestToShipment:',$this->getServiceType());
        $this->_getHelper()->debugging($request,$this->getServiceType());
        return new Varien_Object();
    }

    /**
     * Do return of shipment
     *
     * @param $request
     * @return Varien_Object
     */
    public function returnOfShipment($request)
    {
        $this->_getHelper()->debugging('returnOfShipment:',$this->getServiceType());
        $this->_getHelper()->debugging($request,$this->getServiceType());
        return new Varien_Object();
    }

    /**
     * Function to call Andreani
     *
     * @param Mage_Sales_Model_Order $order
     * @param null $itemsToShip
     *
     * @return bool
     */
    public function doShipmentRequest($order=NULL,$itemsToShip=NULL)
    {
        if($this->isShipmentAvailable())
        {
            try
            {
                $contract    = $this->_getHelper()->getConfigData('contract',$this->getServiceType());

                if ($this->_service == null) {
                    $options = array(
                        'soap_version' => SOAP_1_2,
                        'exceptions' => true,
                        'trace' => 1,
                        'wdsl_local_copy' => true
                    );
                    $username   = $this->_getHelper()->getConfigData('username');
                    $password   = $this->_getHelper()->getConfigData('password');

                    $gatewayUrl = $this->_getHelper()->getConfigData('gateway_url');

                    $this->_getHelper()->debugging('doShipmentRequestDataConnexion:',$this->getServiceType());
                    $this->_getHelper()->debugging(array(
                        'username' => $username,
                        'password' => $password,
                        'gatewayUrl' => $gatewayUrl,
                        'options' => $options
                    ),$this->getServiceType());

                    $wsse_header = Mage::getModel('summa_andreani/api_soap_header', array('username'=> $username, 'password'=>$password));

                    $client = new SoapClient($gatewayUrl, $options);
                    $client->__setSoapHeaders(array($wsse_header));

                    $this->_service = $client;
                } else {
                    $client = $this->_service;
                }

                $detailsProductsSend = $this->_getHelper()->__('Order #').$order->getIncrementId();

                $totalWeight = 0;
                $totalVolume = 0;
                $items = ($itemsToShip===NULL)?$order->getAllItems():$itemsToShip;
                foreach($items as $item)
                {
                    $product = Mage::getModel('catalog/product')->load($item->getProductId());
                    if($product->getTypeId() == "simple"){
                        $totalWeight += $item->getWeight();
                        $totalVolume += ($product->getHeight() * $product->getWidth() * $product->getLength());
                    }
                }

                $totalWeight = $this->_validateWeight($totalWeight);
                $totalVolume = $this->_validateVolume($totalVolume);

                $address = $order->getShippingAddress();

                $number = '-';
                $street = $address->getStreet();
                $DNI_number = $address->getDni();
                $DNI_type = 'DNI';

                $shipmentInfo = array(
                    /* Shipping Data */
                    'SucursalRetiro' => $address->getBranchId() /* Required = Condicional; */
                    ,'Provincia' =>$address->getRegion()
                    ,'Localidad' =>$address->getCity()
                    ,'CodigoPostalDestino' =>$address->getPostcode() /* Required = true; */
                    ,'Calle' =>$street[0] /* Required = true; */
                    ,'Numero' =>$number /* Required = true; */
                    ,'Departamento' =>NULL
                    ,'Piso' =>NULL

                    /* Recipient Data */
                    ,'NombreApellido' =>$address->getFirstname() . ' ' . $address->getLastname() /* Required = true; */
                    ,'TipoDocumento' =>$DNI_type /* Required = true; */
                    ,'NumeroDocumento' =>$DNI_number /* Required = true; */
                    ,'NumeroCelular' =>NULL
                    ,'NumeroTelefono' =>$address->getTelephone()
                    ,'Email' =>$order->getCustomerEmail()
                    ,'NombreApellidoAlternativo' =>NULL

                    /* Delivery Data  */
                    ,'NumeroTransaccion' =>$order->getIncrementId()
                    ,'DetalleProductosEntrega' =>$detailsProductsSend
                    ,'DetalleProductosRetiro' => NULL
                    ,'Peso' =>$totalWeight
                    ,'Volumen' =>$totalVolume /* Required = Condicional;  */
                    ,'ValorACobrar' =>NULL /* Required = Condicional; */
                    ,'ValorDeclarado' =>NULL /* Required = Condicional; */

                    /* Billing Data */
                    ,'Contrato' =>$contract /* Required = true; */
                    ,'SucursalCliente' => NULL/* Required = Condicional; */
                    ,'CategoriaDistancia' =>$order->getRegionId() /* Required = Condicional; */
                    ,'CategoriaFacturacion' =>NULL /* Required = Condicional; */
                    ,'CategoriaPeso' =>NULL /* Required = Condicional; */
                    ,'Tarifa' =>NULL /* Required = Condicional; */

                );

                $this->_getHelper()->debugging('doShipmentRequestDataSent:',$this->getServiceType());
                $this->_getHelper()->debugging($shipmentInfo,$this->getServiceType());

                $andreaniResponse = $client->ConfirmarCompra(array(
                        'compra' => $shipmentInfo
                    )
                );

                $this->_getHelper()->debugging('doShipmentRequestResponse:',$this->getServiceType());
                $this->_getHelper()->debugging($andreaniResponse,$this->getServiceType());

                return($andreaniResponse);
            } catch (SoapFault $e) {
                $error = libxml_get_last_error();
                $error .= "<BR><BR>";
                $error .= $e;

                $this->_getHelper()->debugging('doShipmentRequestError:',$this->getServiceType());
                $this->_getHelper()->debugging($e->getMessage(),$this->getServiceType());
                $this->_getHelper()->debugging($error,$this->getServiceType());

                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * @param      $tracking
     *
     * @return LibXMLError|string
     */
    public function getLinkConstancy($tracking)
    {
        try
        {
            $options = array(
                'soap_version' => SOAP_1_2,
                'exceptions' => true,
                'trace' => 1,
                'wdsl_local_copy' => true
            );

            $username   = $this->_getHelper()->getConfigData('username');
            $password   = $this->_getHelper()->getConfigData('password');
            $gatewayUrl = $this->_getHelper()->getConfigData('gateway_url');

            $this->_getHelper()->debugging('getLinkConstancyDataConnexion:',$this->getServiceType());
            $this->_getHelper()->debugging(array(
                'username' => $username,
                'password' => $password,
                'gatewayUrl' => $gatewayUrl,
                'options' => $options
            ),$this->getServiceType());

            $wsse_header = Mage::getModel('summa_andreani/api_soap_header', array('username'=> $username, 'password'=>$password));
            $client = new SoapClient($gatewayUrl, $options);
            $client->__setSoapHeaders(array($wsse_header));

            $this->_getHelper()->debugging('getLinkConstancyDataSent:',$this->getServiceType());
            $this->_getHelper()->debugging(array(
                'ParamImprimirConstancia' =>array(
                    'NumeroAndreani' =>$tracking
                )
            ),$this->getServiceType());

            $phpresponse = $client->ImprimirConstancia(array(
                'entities' =>array(
                    'ParamImprimirConstancia' =>array(
                        'NumeroAndreani' =>$tracking
                    ))));

            $this->_getHelper()->debugging('getLinkConstancyResponse:',$this->getServiceType());
            $this->_getHelper()->debugging($phpresponse,$this->getServiceType());

            return $phpresponse;
        } catch (SoapFault $e) {
            $error = (libxml_get_last_error());
            $error .= "<BR><BR>";
            $error .= $e;

            $this->_getHelper()->debugging('getLinkConstancyError:',$this->getServiceType());
            $this->_getHelper()->debugging($e->getMessage(),$this->getServiceType());
            $this->_getHelper()->debugging($error,$this->getServiceType());

            return $error;
        }
    }

    /**
     * Get tracking
     *
     * @param mixed $trackings
     * @param $order
     * @return mixed
     */
    public function getTracking($trackings)
    {
        $clientNro  = $this->_getHelper()->getConfigData('client_number');
        $gatewayUrl = $this->_getHelper()->getConfigData('gateway_tracking_url');

        $options = array(
            'soap_version'    => SOAP_1_2,
            'exceptions'      => true,
            'trace'           => 1,
            'wdsl_local_copy' => false,
        );

        try {
            $this->_getHelper()->debugging('getTrackingDataConnexion:',$this->getServiceType());
            $this->_getHelper()->debugging(array(
                'clientNro' => $clientNro,
                'gatewayUrl' => $gatewayUrl,
                'options' => $options
            ),$this->getServiceType());

            $client = new SoapClient($gatewayUrl, $options);

            $this->_getHelper()->debugging('getTrackingDataSent:',$this->getServiceType());
            $this->_getHelper()->debugging(array(
                'Pieza' => array(
                    'NroPieza'      => '',
                    'NroAndreani'   => $trackings,
                    'CodigoCliente' => $clientNro
                )
            ),$this->getServiceType());

            $response = $client->ObtenerTrazabilidad(array(
                'Pieza' => array(
                    'NroPieza'      => '',
                    'NroAndreani'   => $trackings,
                    'CodigoCliente' => $clientNro
                )
            ));

            $this->_getHelper()->debugging('getTrackingResponse:',$this->getServiceType());
            $this->_getHelper()->debugging($response,$this->getServiceType());
        } catch (SoapFault $e) {
            $response = $e;
            $this->_getHelper()->debugging('getTrackingError:',$this->getServiceType());
            $this->_getHelper()->debugging($e->getMessage(),$this->getServiceType());
        }

        $this->_parseXmlTrackingResponse($trackings, $response);

        return $this->_result;
    }

    protected function _parseXmlTrackingResponse($trackings, $response)
    {
        $errorTitle = $this->_getHelper()->__('Unable to retrieve tracking');
        $resultArr  = array();
        $errorArr   = array();
        if (is_object($response)) {
            if(isset($response->faultstring))
            {
                ($response->faultstring != "Pieza inexistente") ?: $errorArr[$trackings] = $errorTitle;
            } else {
                foreach ($response as $pieza) {
                    $envios = is_array($pieza->Envios->Envio) ? $pieza->Envios->Envio : $pieza->Envios;
                    foreach ($envios as $envio) {
                        $tmpArr  = array();
                        $eventos = $envio->Eventos;
                        foreach ($eventos as $evento) {
                            list($date, $time) = $this->_getHelper()->splitDate($evento->Fecha);
                            $tmpArr[] = array(
                                'deliverydate'     => $date,
                                'deliverytime'     => $time,
                                'status'           => $evento->Estado,
                                'deliverylocation' => $evento->Sucursal,
                                'activity'           => $evento->Estado
                            );
                        }

                        list($date, $time) = $this->_getHelper()->splitDate($envio->FechaAlta);
                        $resultArr[$envio->NroAndreani] = array(
                            'deliverydate'   => $date,
                            'deliverytime'   => $time,
                            //'service'        => $envio->NombreEnvio,
                            'progressdetail' => $tmpArr
                        );
                    }
                }
            }
        }else{
            $errorArr[$trackings] = $errorTitle;
        }

        $result = Mage::getModel('shipping/tracking_result');
        if ($errorArr || $resultArr) {
            foreach ($errorArr as $t => $r) {
                $error = Mage::getModel('shipping/tracking_result_error');
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title',$this->getServiceType()));
                $error->setTracking($t);
                $error->setErrorMessage($r);
                $result->append($error);
            }

            foreach ($resultArr as $t => $data) {
                $tracking = Mage::getModel('shipping/tracking_result_status');
                $tracking->setCarrier($this->_code);
                $tracking->setCarrierTitle($this->getConfigData('title',$this->getServiceType()));
                $tracking->setTracking($t);
                $tracking->addData($data);

                $result->append($tracking);
            }
        } else {
            foreach ($trackings as $t) {
                $error = Mage::getModel('shipping/tracking_result_error');
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title',$this->getServiceType()));
                $error->setTracking($t);
                $error->setErrorMessage($errorTitle);
                $result->append($error);

            }
        }
        $this->_result = $result;
    }

    /**
     * Function to cancel Shipment request to andreani
     * @param $nroAndreani
     *
     * @return bool
     */
    public function cancelShipmentRequest($nroAndreani)
    {
        try {
            $options = array(
                'soap_version' => SOAP_1_2,
                'exceptions' => true,
                'trace' => 1,
                'wdsl_local_copy' => true
            );

            $username   = $this->_getHelper()->getConfigData('username');
            $password   = $this->_getHelper()->getConfigData('password');
            $gatewayUrl = $this->_getHelper()->getConfigData('gateway_url');

            $this->_getHelper()->debugging('cancelShipmentRequestDataConnexion:',$this->getServiceType());
            $this->_getHelper()->debugging(array(
                'username' => $username,
                'password' => $password,
                'gatewayUrl' => $gatewayUrl,
                'options' => $options
            ),$this->getServiceType());

            $wsse_header = Mage::getModel('grandmarche_andreani/api_soap_header', array('username'=> $username, 'password'=>$password));

            $client = new SoapClient($gatewayUrl, $options);
            $client->__setSoapHeaders(array($wsse_header));

            $cancelShipmentInfo = array(
                'envios' => array(
                    'ParamAnularEnvios' => array(
                        'NumeroAndreani' => $nroAndreani
                    )
                )
            );
            $this->_getHelper()->debugging('cancelShipmentRequestDataSent:',$this->getServiceType());
            $this->_getHelper()->debugging($cancelShipmentInfo,$this->getServiceType());

            $response = $client->AnularEnvios($cancelShipmentInfo);

            $this->_getHelper()->debugging('cancelShipmentRequestResponse:',$this->getServiceType());
            $this->_getHelper()->debugging($response,$this->getServiceType());

            return ($response);

        } catch (SoapFault $e) {
            $error = libxml_get_last_error();
            $error .= "<BR><BR>";
            $error .= $e;

            $this->_getHelper()->debugging('cancelShipmentRequestError:',$this->getServiceType());
            $this->_getHelper()->debugging($e->getMessage(),$this->getServiceType());
            $this->_getHelper()->debugging($error,$this->getServiceType());

            return false;
        }
    }

    /**
     * Function to return Rates availables for the request
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return bool|Mage_Shipping_Model_Rate_Result|null
     */
    protected function _collectRatesByWebService(Mage_Shipping_Model_Rate_Request $request)
    {
        // TODO: Implement collect rates using web service.
        // TODO: Add insurance if it is enabled.
        return false;
    }
    /**
     * Function to return Rates availables for the request
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return bool|Mage_Shipping_Model_Rate_Result|null
     */
    protected function _collectRatesByMatrixRates(Mage_Shipping_Model_Rate_Request $request)
    {
        /** @var Mage_Shipping_Model_Rate_Result $result */
        $result = Mage::getModel('shipping/rate_result');

        $request = $this->_processRequestForMatrixRates($request);

        $request->setShippingType($this->_shippingTypeForMatrixrates);

        $rateArray = $this->_getRateArrayFromMatrixrates($request);

        $insurance = 0;
        if ($this->_getHelper()->getConfigData('apply_insurance_on_shipping_price')) {
            $insurance = $request->getPackageValue() * $this->_getHelper()->getConfigData('insurance') / 100;
        }

        foreach ($rateArray as $rate)
        {
            if (!empty($rate) && $rate['delivery_type'] == $this->_shippingTypeForMatrixrates && $rate['price'] >= 0) {
                /** @var $method Mage_Shipping_Model_Rate_Result_Method */
                $method = Mage::getModel('shipping/rate_result_method');

                $method->setCarrier($this->_code);
                $method->setCarrierTitle($this->_getHelper()->getConfigData('title',$this->getServiceType()));

                $method->setMethod($this->_code);

                $method->setMethodTitle($this->_getHelper()->getConfigData('name',$this->getServiceType()));

                $method->setCost($rate['cost']);
                $method->setDeliveryType($rate['delivery_type']);

                $price = $rate['price'];

                if ($insurance) {
                    $price += $insurance;
                }

                $method->setPrice($this->getFinalPriceWithHandlingFee($price));

                $result->append($method);
            }
        }

        return $result;
    }

    /**
     * Function to get Rate Array from MatrixRates
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return array
     */
    protected function _getRateArrayFromMatrixrates(Mage_Shipping_Model_Rate_Request $request)
    {
        /** @var Webshopapps_Matrixrate_Model_Mysql4_Carrier_Matrixrate $resourceMatrixrate */
        $resourceMatrixrate = Mage::getResourceModel('matrixrate_shipping/carrier_matrixrate');
        $rate = $resourceMatrixrate->getNewRate($request);
        return $rate;
    }

    /**
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return Mage_Shipping_Model_Rate_Request
     */
    protected function _processRequestForMatrixRates(Mage_Shipping_Model_Rate_Request $request)
    {
        // exclude Virtual products price from Package value if pre-configured
        if (!$this->_getHelper()->getConfigDataFromMatrixRates('include_virtual_price') && $request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getProduct()->isVirtual() || $item->getProductType() == 'downloadable') {
                            $request->setPackageValue($request->getPackageValue() - $child->getBaseRowTotal());
                        }
                    }
                } elseif ($item->getProduct()->isVirtual() || $item->getProductType() == 'downloadable') {
                    $request->setPackageValue($request->getPackageValue() - $item->getBaseRowTotal());
                }
            }
        }

        // Free shipping by qty
        $freeQty = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    foreach ($item->getChildren() as $child) {
                        if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                            $freeQty += $item->getQty() * ($child->getQty() - (is_numeric($child->getFreeShipping()) ? $child->getFreeShipping() : 0));
                        }
                    }
                } elseif ($item->getFreeShipping()) {
                    $freeQty += ($item->getQty() - (is_numeric($item->getFreeShipping()) ? $item->getFreeShipping() : 0));
                }
            }
        }

        if (!$request->getMRConditionName()) {
            $request->setMRConditionName($this->_getHelper()->getConfigDataFromMatrixRates('condition_name') ? $this->_getHelper()->getConfigDataFromMatrixRates('condition_name') : $this->_default_condition_name);
        }

        // Package weight and qty free shipping
        $oldWeight = $request->getPackageWeight();
        $oldQty = $request->getPackageQty();

        if ($this->_getHelper()->getConfigDataFromMatrixRates('allow_free_shipping_promotions') && !$this->_getHelper()->getConfigDataFromMatrixRates('include_free_ship_items')) {
            $request->setPackageWeight($request->getFreeMethodWeight());
            $request->setPackageQty($oldQty - $freeQty);
        }

        // clean city and postcode
        $request->setDestCity('');
        $request->setDestPostcode('');

        return $request;
    }

    /**
     * Function to get Helper Instance
     * @return Summa_Andreani_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('summa_andreani');
    }

    /**
     * Function to Validate Package Weight
     * @param $weight
     *
     * @return int
     */
    protected function _validateWeight($weight)
    {
        if (is_null($this->_limitWeight)) {
            $this->_limitWeight = $this->_getHelper()->getConfigData('limit_weight');
        }
        if ($weight > $this->_limitWeight) {
            $this->_getHelper()->throwException('Weight limit overflow.',$this->getServiceType());
        }
        if (!$weight || $weight < 0 || is_null($weight)) {
            $weight = 1;
        }
        return $weight;
    }

    /**
     * Function to validate Package Volume
     * @param $volume
     *
     * @return int
     */
    protected function _validateVolume($volume)
    {
        if (!$volume || $volume < 0 || is_null($volume)) {
            $volume = 1;
        }
        return intval($volume);
    }

    /**
     * Function to return Service Type
     * @return string
     */
    public function getServiceType()
    {
        return $this->_serviceType;
    }

    /**
     * Function to return Code
     * @return string
     */
    public function getCode()
    {
        return $this->_code;
    }
}
