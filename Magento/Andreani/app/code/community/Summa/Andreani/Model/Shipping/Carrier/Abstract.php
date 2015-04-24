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

    protected $_code = 'andreaniAbstract';
    protected $_freeShipping_method = 'free';
    protected $_default_condition_name = 'package_weight';
    protected $_service = null;
    protected $_serviceType = 'global';
    protected $_result;
    protected $_limitWeight = null;
    protected $_shippingTypeForMatrixrates = 'Abstract';

    /**
     * Function to return status of tracking
     *
     * @return bool
     */
    public function isTrackingAvailable()
    {
        return $this->_getHelper()->getConfigData('is_tracking_enabled',$this->getServiceType());
    }

    /**
     * Check if carrier has shipping label option available
     *
     * @return boolean
     */
    public function isShippingLabelsAvailable()
    {
        return true;
    }

    /**
     * Function to return status of Shipment
     *
     * @return bool
     */
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
        if (!$this->isShipmentAvailable()) {
            return false;
        }

        if ((int)$this->_getHelper()->getConfigData('source_rates') === Summa_Andreani_Model_System_Config_Source_Shipping_Service::ANDREANI_WEBSERVICE_VALUE) {
            return $this->_collectRatesByWebService($request);
        } else {
            return $this->_collectRatesByMatrixRates($request);
        }
    }

    /**
     * Do request to shipment
     * @deprecated because Andreani don't support shipping label generation
     * @param Mage_Shipping_Model_Shipment_Request $request
     *
     * @return Varien_Object
     */
    public function requestToShipment(Mage_Shipping_Model_Shipment_Request $request)
    {
        $this->_getHelper()->debugging('requestToShipment:', $this->getServiceType());
        $this->_getHelper()->debugging($request, $this->getServiceType());

        $packages = $request->getPackages();
        if (!is_array($packages) || !$packages) {
            Mage::throwException(Mage::helper('summa_andreani')->__('No packages for request'));
        }
        $data = array();
        $helper = $this->_getShipmentsHelper();
        foreach ($packages as $packageId => $package) {

            $this->_getHelper()->debugging('requestToShipmentDoShipmentRequestParams:', $this->getServiceType());
            $this->_getHelper()->debugging($request->getOrderShipment()->getOrder(), $this->getServiceType());
            $this->_getHelper()->debugging($package['items'], $this->getServiceType());

            $result = $this->doShipmentRequest($request->getOrderShipment()->getOrder(), $package['items'],$package['params']);

            $this->_getHelper()->debugging('requestToShipmentDoShipmentRequestResult:', $this->getServiceType());
            $this->_getHelper()->debugging($result, $this->getServiceType());

            if ($result->hasErrors()) {
                break;
            } else {
                $data[] = array(
                    'tracking_number' => $result->getTrackingNumber(),
                    'label_content'   => $helper->preparePdf($result->getShippingLabelContent())
                );
            }
        }
        $response = new Varien_Object(array(
            'info' => $data
        ));

        return $response;
    }


    /**
     * Do return of shipment
     * @deprecated because Andreani don't support shipping label generation for a return
     *
     * @param $request
     *
     * @return Varien_Object
     */
    /*
    public function returnOfShipment($request)
    {
        $this->_getHelper()->debugging('returnOfShipment:', $this->getServiceType());
        $this->_getHelper()->debugging($request, $this->getServiceType());

        $packages = $request->getPackages();
        if (!is_array($packages) || !$packages) {
            Mage::throwException(Mage::helper('summa_andreani')->__('No packages for request'));
        }
        $data = array();

        foreach ($packages as $packageId => $package) {
            $this->_getHelper()->debugging('returnOfShipmentCancelShipmentRequestParams:',$this->getServiceType());
            //$this->_getHelper()->debugging(,$this->getServiceType());

            $result = $this->returnShipmentRequest($request->getOrderShipment()->getOrder(),$package['items']);

            $this->_getHelper()->debugging('returnOfShipmentCancelShipmentRequestResult:',$this->getServiceType());
            $this->_getHelper()->debugging($result,$this->getServiceType());

            if ($result->hasErrors()) {
                break;
            } else {
                $data[] = array(
                    'tracking_number' => $result->getTrackingNumber(),
                    'label_content'   => $result->getShippingLabelContent()
                );
            }
        }

        $response = new Varien_Object(array(
            'info' => $data
        ));

        return $response;
    }
    */

    /**
     * Function to call Andreani
     *
     * @param Mage_Sales_Model_Order $order
     * @param null                   $itemsToShip
     * @param null|array             $params
     *
     * @return Varien_Object
     */
    public function doShipmentRequest($order = NULL, $itemsToShip = NULL, $params = NULL)
    {
        $result = new Varien_Object();
        if ($this->isShipmentAvailable()) {
            try {
                $contract = $this->_getHelper()->getContract($this->getServiceType());

                if ($this->_service == null) {
                    $options = $this->_getHelper()->getSoapOptions();
                    $username = $this->_getHelper()->getUsername($this->getServiceType());
                    $password = $this->_getHelper()->getPassword($this->getServiceType());

                    $gatewayUrl = $this->_getHelper()->getConfigData('gateway_url');

                    $this->_getHelper()->debugging('doShipmentRequestDataConnexion:', $this->getServiceType());
                    $this->_getHelper()->debugging(array(
                        'username'   => $username,
                        'password'   => $password,
                        'gatewayUrl' => $gatewayUrl,
                        'options'    => $options
                    ), $this->getServiceType());

                    $wsse_header = $this->_getHelper()->getWsseHeader($username,$password);

                    $client = new SoapClient($gatewayUrl, $options);
                    $client->__setSoapHeaders(array($wsse_header));

                    $this->_service = $client;
                } else {
                    $client = $this->_service;
                }

                $detailsProductsSend = $this->_getHelper()->__('Order #') . $order->getIncrementId();
                $items = ($itemsToShip === NULL) ? $order->getAllItems() : $itemsToShip;
                if (is_null($params)) {
                    $totals = $this->getTotalsWVFromItems($items);
                } else {
                    $totals = $this->getTotalsWVFromParams($params);
                }

                $address = $order->getShippingAddress();

                $streets = $address->getStreet();

                $shipmentInfo = array(
                    /* Shipping Data */
                    'SucursalRetiro'          => $address->getAndreaniBranchId() /* Required = Condicional; */
                    , 'Provincia'                 => $address->getRegion()
                    , 'Localidad'                 => $address->getCity()
                    , 'CodigoPostalDestino'       => $address->getPostcode() /* Required = true; */
                    , 'Calle'                     => $streets[0] /* Required = true; */
                    , 'Numero'                    => '-' /* Required = true; */
                    , 'Departamento'              => NULL
                    , 'Piso'                      => NULL

                        /* Recipient Data */
                    , 'NombreApellido'            => $address->getFirstname() . ' ' . $address->getLastname() /* Required = true; */
                    , 'TipoDocumento'             => 'DNI' /* Required = true; */
                    , 'NumeroDocumento'           => 'xxxxxxxx' /* Required = true; */
                    , 'NumeroCelular'             => NULL
                    , 'NumeroTelefono'            => $address->getTelephone()
                    , 'Email'                     => $order->getCustomerEmail()
                    , 'NombreApellidoAlternativo' => NULL

                        /* Delivery Data  */
                    , 'NumeroTransaccion'         => $order->getIncrementId()
                    , 'DetalleProductosEntrega'   => $detailsProductsSend
                    , 'DetalleProductosRetiro'    => NULL
                    , 'Peso'                      => $totals->getTotalWeight()
                    , 'Volumen'                   => $totals->getTotalVolume() /* Required = Condicional;  */
                    , 'ValorACobrar'              => NULL /* Required = Condicional; */
                    , 'ValorDeclarado'            => NULL /* Required = Condicional; */

                        /* Billing Data */
                    , 'Contrato'                  => $contract /* Required = true; */
                    , 'SucursalCliente'           => NULL/* Required = Condicional; */
                    , 'CategoriaDistancia'        => $order->getRegionId() /* Required = Condicional; */
                    , 'CategoriaFacturacion'      => NULL /* Required = Condicional; */
                    , 'CategoriaPeso'             => NULL /* Required = Condicional; */
                    , 'Tarifa'                    => NULL /* Required = Condicional; */
                );

                $this->_getHelper()->debugging('doShipmentRequestDataSent:', $this->getServiceType());
                $this->_getHelper()->debugging($shipmentInfo, $this->getServiceType());

                $andreaniResponse = $client->ConfirmarCompra(array(
                        'compra' => $shipmentInfo
                    )
                );

                $this->_getHelper()->debugging('doShipmentRequestResponse:', $this->getServiceType());
                $this->_getHelper()->debugging($andreaniResponse, $this->getServiceType());

                if (!$andreaniResponse->ConfirmarCompraResult || !$andreaniResponse->ConfirmarCompraResult->NumeroAndreani) {
                    $result->setErrors($this->_getHelper()->__('Service unavailable. The service %s with code %s returns unexpected data.', $this->_getHelper()->getConfigData('title', $this->getServiceType()), $this->getCode()));
                } else {
                    $result->setTrackingNumber($andreaniResponse->ConfirmarCompraResult->NumeroAndreani);
                    $linkConstancy = $this->getLinkConstancy($result->getTrackingNumber());
                    if (isset($linkConstancy->ImprimirConstanciaResult)) {
                        $result->setShippingLabelContent($linkConstancy->ImprimirConstanciaResult->ResultadoImprimirConstancia->PdfLinkFile);
                    } else {
                        $result->setShippingLabelContent($this->_getHelper()->__('Receive') . ' ' . $andreaniResponse->ConfirmarCompraResult->Recibo);
                    }
                }

                return $result;
            } catch (SoapFault $e) {
                $error = libxml_get_last_error();
                $error .= "<BR><BR>";
                $error .= $e;

                $result->setErrors($e->getMessage());

                $this->_getHelper()->debugging('doShipmentRequestError:', $this->getServiceType());
                $this->_getHelper()->debugging($e->getMessage(), $this->getServiceType());
                $this->_getHelper()->debugging($error, $this->getServiceType());

                return $result;
            }
        } else {
            $result->setErrors($this->_getHelper()->__('Service unavailable. You mus\'t set enabled the service %s with code %s', $this->_getHelper()->getConfigData('title', $this->getServiceType()), $this->getCode()));

            return $result;
        }
    }

    /**
     * @param      $tracking
     *
     * @return LibXMLError|string
     */
    public function getLinkConstancy($tracking)
    {
        try {
            $options = $this->_getHelper()->getSoapOptions();
            $username = $this->_getHelper()->getUsername($this->getServiceType());
            $password = $this->_getHelper()->getPassword($this->getServiceType());
            $gatewayUrl = $this->_getHelper()->getConfigData('gateway_url');

            $this->_getHelper()->debugging('getLinkConstancyDataConnexion:', $this->getServiceType());
            $this->_getHelper()->debugging(array(
                'username'   => $username,
                'password'   => $password,
                'gatewayUrl' => $gatewayUrl,
                'options'    => $options
            ), $this->getServiceType());

            $wsse_header = $this->_getHelper()->getWsseHeader($username,$password);
            $client = new SoapClient($gatewayUrl, $options);
            $client->__setSoapHeaders(array($wsse_header));

            $dataSent = array(
                'entities' => array(
                    'ParamImprimirConstancia' => array(
                        'NumeroAndreani' => $tracking
                    )
                )
            );
            $this->_getHelper()->debugging('getLinkConstancyDataSent:', $this->getServiceType());
            $this->_getHelper()->debugging($dataSent, $this->getServiceType());

            $phpresponse = $client->ImprimirConstancia($dataSent);

            $this->_getHelper()->debugging('getLinkConstancyResponse:', $this->getServiceType());
            $this->_getHelper()->debugging($phpresponse, $this->getServiceType());

            return $phpresponse;
        } catch (SoapFault $e) {
            $error = (libxml_get_last_error());
            $error .= "<BR><BR>";
            $error .= $e;

            $this->_getHelper()->debugging('getLinkConstancyError:', $this->getServiceType());
            $this->_getHelper()->debugging($e->getMessage(), $this->getServiceType());
            $this->_getHelper()->debugging($error, $this->getServiceType());

            return $error;
        }
    }

    public function getTrackingInfo($tracking)
    {
        $result = $this->getTracking($tracking);

        if($result instanceof Mage_Shipping_Model_Tracking_Result){
            if ($tracks = $result->getAllTrackings()) {
                return $tracks[0];
            }
        }
        elseif (is_string($result) && !empty($result)) {
            return $result;
        }

        return false;
    }

    /**
     * Get tracking
     *
     * @param mixed $trackings
     *
     * @return mixed
     */
    public function getTracking($tracks)
    {
        $clientNro = $this->_getHelper()->getClientNumber($this->getServiceType());
        $gatewayUrl = $this->_getHelper()->getConfigData('gateway_tracking_url');

        $options = $this->_getHelper()->getSoapOptions();

        try {
            $this->_getHelper()->debugging('getTrackingDataConnexion:', $this->getServiceType());
            $this->_getHelper()->debugging(array(
                'clientNro'  => $clientNro,
                'gatewayUrl' => $gatewayUrl,
                'options'    => $options
            ), $this->getServiceType());

            $client = new SoapClient($gatewayUrl, $options);

            $this->_getHelper()->debugging('getTrackingDataSent:', $this->getServiceType());
            $this->_getHelper()->debugging(array(
                'Pieza' => array(
                    'NroPieza'      => '',
                    'NroAndreani'   => $tracks,
                    'CodigoCliente' => $clientNro
                )
            ), $this->getServiceType());

            $response = $client->ObtenerTrazabilidad(array(
                'Pieza' => array(
                    'NroPieza'      => '',
                    'NroAndreani'   => $tracks,
                    'CodigoCliente' => $clientNro
                )
            ));

            $this->_getHelper()->debugging('getTrackingResponse:', $this->getServiceType());
            $this->_getHelper()->debugging($response, $this->getServiceType());
        } catch (SoapFault $e) {
            $response = $e;
            $this->_getHelper()->debugging('getTrackingError:', $this->getServiceType());
            $this->_getHelper()->debugging($e->getMessage(), $this->getServiceType());
        }

        $this->_parseXmlTrackingResponse($tracks, $response);

        return $this->_result;
    }

    /**
     * Function to parse XML response from Andreani Web Services
     * And Update status of shipment if it's needed
     * @param $tracks
     * @param $response
     */
    protected function _parseXmlTrackingResponse($tracks, $response)
    {
        $errorTitle = $this->_getHelper()->__('Unable to retrieve tracking');
        $resultArr = array();
        $errorArr = array();
        if (is_object($response)) {
            if($response instanceof SoapFault)
            {
                $errorArr[$tracks] = $errorTitle. ". " . $response->getMessage();
            } else {
                foreach ($response as $pieza) {
                    $envios = is_array($pieza->Envios->Envio) ? $pieza->Envios->Envio : $pieza->Envios;
                    foreach ($envios as $envio) {
                        /** @var Mage_Sales_Model_Order_Shipment_Track $track */
                        $track = Mage::getModel('sales/order_shipment_track')->load($envio->NroAndreani,'track_number');
                        $checkStatus = true;
                        if ($track->getShipment()->getSummaAndreaniShipmentStatus() == Summa_Andreani_Model_Status::SHIPMENT_COMPLETED){
                            $checkStatus = false; // if Shipment Status is Completed this flag avoid check status
                        }
                        $tmpArr  = array();
                        $eventos = $envio->Eventos;
                        foreach ($eventos as $evento) {
                            if (is_array($evento)) {
                                foreach ($evento as $status) {
                                    list($date, $time) = $this->_getHelper()->splitDate($status->Fecha);
                                    $tmpArr[] = array(
                                        'deliverydate'     => $date,
                                        'deliverytime'     => $time,
                                        'status'           => $status->Estado,
                                        'deliverylocation' => $status->Sucursal,
                                        'activity'           => $status->Estado
                                    );
                                    if ($checkStatus) {
                                        $this->_getHelper()->getStatusSingleton()->checkStatus($track,$status->Estado);
                                    }
                                }
                            } else {
                                list($date, $time) = $this->_getHelper()->splitDate($evento->Fecha);
                                $tmpArr[] = array(
                                    'deliverydate'     => $date,
                                    'deliverytime'     => $time,
                                    'status'           => $evento->Estado,
                                    'deliverylocation' => $evento->Sucursal,
                                    'activity'           => $evento->Estado
                                );
                                if ($checkStatus) {
                                    $this->_getHelper()->getStatusSingleton()->checkStatus($track,$evento->Estado);
                                }
                            }
                        }

                        list($date, $time) = $this->_getHelper()->splitDate($envio->FechaAlta);
                        $resultArr[$envio->NroAndreani] = array(
                            'deliverydate'   => $date,
                            'deliverytime'   => $time,
                            //'service'        => $envio->NombreEnvio,
                            'progressdetail' => $tmpArr
                        );
                        if (count($tmpArr) && $checkStatus) { // Check status and update status of shipment if it's needing it
                            $results = $this->_getHelper()->getStatusSingleton()->getCheckedStatuses();

                            $status = $results[$track->getShipment()->getId()];

                            if ($status->getIsStatusUpdateRequired())
                            {
                                $track->getShipment()
                                    ->setSummaAndreaniShipmentStatus($status->getStatusToUpdate())
                                    ->save();
                            }
                        }
                    }
                }
            }
        } else {
            $errorArr[$tracks] = $errorTitle;
        }

        $result = Mage::getModel('shipping/tracking_result');
        if ($errorArr || $resultArr) {
            foreach ($errorArr as $t => $r) {
                /** @var Mage_Shipping_Model_Tracking_Result_Error $error */
                $error = Mage::getModel('shipping/tracking_result_error');
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title', $this->getServiceType()));
                $error->setTracking($t);
                $error->setErrorMessage($r);
                $result->append($error);
            }

            foreach ($resultArr as $t => $data) {
                /** @var Mage_Shipping_Model_Tracking_Result_Status $tracking */
                $tracking = Mage::getModel('shipping/tracking_result_status');
                $tracking->setCarrier($this->_code);
                $tracking->setCarrierTitle($this->getConfigData('title', $this->getServiceType()));
                $tracking->setTracking($t);
                $tracking->addData($data);

                $result->append($tracking);
            }
        } else {
            foreach ($tracks as $t) {
                /** @var Mage_Shipping_Model_Tracking_Result_Error $error */
                $error = Mage::getModel('shipping/tracking_result_error');
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title', $this->getServiceType()));
                $error->setTracking($t);
                $error->setErrorMessage($errorTitle);
                $result->append($error);

            }
        }
        $this->_result = $result;
    }

    /**
     * Function to cancel Shipment request to andreani
     *
     * @param $nroAndreani
     *
     * @return bool
     */
    public function cancelShipmentRequest($nroAndreani)
    {
        try {
            $options = $this->_getHelper()->getSoapOptions();
            $username = $this->_getHelper()->getUsername($this->getServiceType());
            $password = $this->_getHelper()->getPassword($this->getServiceType());
            $gatewayUrl = $this->_getHelper()->getConfigData('gateway_url');

            $this->_getHelper()->debugging('cancelShipmentRequestDataConnexion:', $this->getServiceType());
            $this->_getHelper()->debugging(array(
                'username'   => $username,
                'password'   => $password,
                'gatewayUrl' => $gatewayUrl,
                'options'    => $options
            ), $this->getServiceType());

            $wsse_header = $this->_getHelper()->getWsseHeader($username,$password);

            $client = new SoapClient($gatewayUrl, $options);
            $client->__setSoapHeaders(array($wsse_header));

            $cancelShipmentInfo = array(
                'envios' => array(
                    'ParamAnularEnvios' => array(
                        'NumeroAndreani' => $nroAndreani
                    )
                )
            );
            $this->_getHelper()->debugging('cancelShipmentRequestDataSent:', $this->getServiceType());
            $this->_getHelper()->debugging($cancelShipmentInfo, $this->getServiceType());

            $response = $client->AnularEnvios($cancelShipmentInfo);

            $this->_getHelper()->debugging('cancelShipmentRequestResponse:', $this->getServiceType());
            $this->_getHelper()->debugging($response, $this->getServiceType());

            return ($response);

        } catch (SoapFault $e) {
            $error = libxml_get_last_error();
            $error .= "<BR><BR>";
            $error .= $e;

            $this->_getHelper()->debugging('cancelShipmentRequestError:', $this->getServiceType());
            $this->_getHelper()->debugging($e->getMessage(), $this->getServiceType());
            $this->_getHelper()->debugging($error, $this->getServiceType());

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
        /** @var Mage_Shipping_Model_Rate_Result $result */
        $result = Mage::getModel('shipping/rate_result');

        $request = $this->_processRequestForFreeShipping($request);

        $freeShipping = $request->getIsFreeShipping();

        if ($freeShipping)
        {
            /** @var $method Mage_Shipping_Model_Rate_Result_Method */
            $method = Mage::getModel('shipping/rate_result_method');
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->_getHelper()->getConfigData('title', $this->getServiceType()));
            $method->setMethod($this->_freeShipping_method);
            $method->setMethodTitle($this->_getHelper()->getFreeMethodText($this->getServiceType()));
            $method->setPrice('0.00');
            $result->append($method);

            if ($this->getConfigData('show_only_free')) {
                return $result;
            }
        }

        $contract = $this->_getHelper()->getContract($this->getServiceType());
        $clientNumber = $this->_getHelper()->getClientNumber($this->getServiceType());

        $options = $this->_getHelper()->getSoapOptions();
        $username = $this->_getHelper()->getUsername($this->getServiceType());
        $password = $this->_getHelper()->getPassword($this->getServiceType());

        $gatewayUrl = $this->_getHelper()->getConfigData('gateway_rates_url');

        $this->_getHelper()->debugging('collectRatesByWebServiceDataConnexion:', $this->getServiceType());
        $this->_getHelper()->debugging(array(
            'username'        => $username,
            'password'        => $password,
            'gatewayRatesUrl' => $gatewayUrl,
            'options'         => $options
        ), $this->getServiceType());

        $wsse_header = $this->_getHelper()->getWsseHeader($username,$password);

        $client = new SoapClient($gatewayUrl, $options);
        $client->__setSoapHeaders(array($wsse_header));

        $insurance = 0;
        if ($this->_getHelper()->getConfigData('apply_insurance_on_shipping_price')) {
            $insurance = $this->_getHelper()->calculateInsurance($request->getPackageValue());
        }
        $totals = $this->getTotalsWVFromItems($request->getAllItems());
        $responseWS = array();
        $collectRatesInfo = array(
            'cotizacionEnvio' => array(
                'CPDestino'      => $request->getDestPostcode(),
                'Cliente'        => $clientNumber,
                'Contrato'       => $contract,
                'Peso'           => $totals->getTotalWeight(),
                'ValorDeclarado' => '', // Optional
                'Volumen'        => $totals->getTotalVolume()
            )
        );
        $this->_getHelper()->debugging('collectRatesByWebServiceDataSent:', $this->getServiceType());
        $this->_getHelper()->debugging($collectRatesInfo, $this->getServiceType());

        $responseWS[] = $this->_parseRatesFromWebService($client->CotizarEnvio($collectRatesInfo), $insurance);

        $this->_getHelper()->debugging('collectRatesByWebServiceResponse:', $this->getServiceType());
        $this->_getHelper()->debugging($responseWS, $this->getServiceType());

        /** @var $rate Varien_Object */
        foreach ($responseWS as $rate) {
            if (!$rate->hasErrors()) {
                /** @var $method Mage_Shipping_Model_Rate_Result_Method */
                $method = Mage::getModel('shipping/rate_result_method');

                $method->setCarrier($this->_code);
                $method->setCarrierTitle($this->_getHelper()->getConfigData('title', $this->getServiceType()));

                $method->setMethod($this->_code);

                $methodTitle = $this->_getHelper()->getConfigData('name', $this->getServiceType());

                $method->setMethodTitle($methodTitle);

                $method->setCost(0);

                $method->setPrice($this->getFinalPriceWithHandlingFee($rate->getPrice()));

                $result->append($method);
            }
        }

        return $result;
    }

    /**
     * Function to parse object information responses from web service with
     * rates to Magento Varien Object Rate.
     * @param      $ratesFromWS
     * @param int  $insurance
     * @param null $branchId
     *
     * @return Varien_Object
     */
    protected function _parseRatesFromWebService($ratesFromWS, $insurance = 0, $branchId = null)
    {
        $this->_getHelper()->debugging('ParseRatesFromWebServiceDataReceived:', $this->getServiceType());
        $this->_getHelper()->debugging(array(
            'ratesFromWS'        => $ratesFromWS,
            'insurance'        => $insurance,
            'branchId' => $branchId
        ), $this->getServiceType());
        $response = new Varien_Object();
        try{
            if (!isset($ratesFromWS->CotizarEnvioResult)) {
                $response->setErrors($this->_getHelper()->__('Web Service did not return rates.'));
            } else {
                $response->setDistanceCategory($ratesFromWS->CotizarEnvioResult->CategoriaDistancia);
                $response->setDistanceCategoryId($ratesFromWS->CotizarEnvioResult->CategoriaDistanciaId);
                $response->setWeightCategory($ratesFromWS->CotizarEnvioResult->CategoriaPeso);
                $response->setWeightCategoryId($ratesFromWS->CotizarEnvioResult->CategoriaPesoId);
                $response->setCalculatedWeight($ratesFromWS->CotizarEnvioResult->PesoAforado);
                $response->setPrice($ratesFromWS->CotizarEnvioResult->Tarifa + $insurance);
                $response->setBranch($branchId);
            }
        } catch (Exception $e) {
            $response->setErrors($this->_getHelper()->__('Web Service did not return rates.'));
        }
        $this->_getHelper()->debugging('ParseRatesFromWebServiceResponse:', $this->getServiceType());
        $this->_getHelper()->debugging($response, $this->getServiceType());
        return $response;
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

        $freeShipping = $request->getIsFreeShipping();

        if ($freeShipping)
        {
            /** @var $method Mage_Shipping_Model_Rate_Result_Method */
            $method = Mage::getModel('shipping/rate_result_method');
            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->_getHelper()->getConfigData('title', $this->getServiceType()));
            $method->setMethod($this->_freeShipping_method);
            $method->setMethodTitle($this->_getHelper()->getFreeMethodText($this->getServiceType()));
            $method->setPrice('0.00');
            $result->append($method);

            if ($this->getConfigData('show_only_free')) {
                return $result;
            }
        }

        $request->setShippingType($this->_getHelper()->getConfigData('shipping_type_for_matrixrates',$this->getServiceType()));

        $rateArray = $this->_getRateArrayFromMatrixrates($request);

        $insurance = 0;
        if ($this->_getHelper()->getConfigData('apply_insurance_on_shipping_price')) {
            $insurance = $this->_getHelper()->calculateInsurance($request->getPackageValue());
        }

        $iva = 0;
        if ($this->_getHelper()->getConfigData('add_iva_to_rates')) {
            $iva = $this->_getHelper()->calculateIVA($request->getPackageValue());
        }

        foreach ($rateArray as $rate) {
            if (!empty($rate) && $rate['delivery_type'] == $this->_shippingTypeForMatrixrates && $rate['price'] >= 0) {
                /** @var $method Mage_Shipping_Model_Rate_Result_Method */
                $method = Mage::getModel('shipping/rate_result_method');

                $method->setCarrier($this->_code);
                $method->setCarrierTitle($this->_getHelper()->getConfigData('title', $this->getServiceType()));

                $method->setMethod($this->_code);

                $method->setMethodTitle($this->_getHelper()->getConfigData('name', $this->getServiceType()));

                $method->setCost($rate['cost']);
                $method->setDeliveryType($rate['delivery_type']);

                $price = $rate['price'];

                if ($insurance) {
                    $price += $insurance;
                }

                if ($iva) {
                    $price += $iva;
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
    protected function _preprocessRequestForMatrixRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $totals = $this->getTotalsWVFromItems($request->getAllItems());
        $request->setPackageWeight($totals->getTotalWeight());
        $request->setPackageHeight($totals->getTotalHeight());
        $request->setPackageWidth($totals->getTotalWidth());
        $request->setPackageDepth($totals->getTotalLength());
    }

    /**
     *
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return Mage_Shipping_Model_Rate_Request
     */
    protected function _processRequestForMatrixRates(Mage_Shipping_Model_Rate_Request $request)
    {
        $request = $this->_preprocessRequestForMatrixRates($request);
        $request = $this->_processRequestForFreeShipping($request);

        if (!$request->getMRConditionName()) {
            $request->setMRConditionName($this->_getHelper()->getConfigData('condition_name') ? $this->_getHelper()->getConfigData('condition_name') : $this->_default_condition_name);
        }

        $conditionsToClean = explode(',',$this->_getHelper()->getConfigData('conditionable_filters'));

        if (!in_array(Summa_Andreani_Model_System_Config_Source_Shipping_MatrixratesFilter::COUNTRY_VALUE,$conditionsToClean)) {
            $request->setDestCountryId('');
        }
        if (!in_array(Summa_Andreani_Model_System_Config_Source_Shipping_MatrixratesFilter::REGION_VALUE,$conditionsToClean)) {
            $request->setDestRegionId('');
            $request->setDestRegionCode('');
        }
        if (!in_array(Summa_Andreani_Model_System_Config_Source_Shipping_MatrixratesFilter::CITY_VALUE,$conditionsToClean)) {
            $request->setDestCity('');
        }
        if (!in_array(Summa_Andreani_Model_System_Config_Source_Shipping_MatrixratesFilter::ZIP_VALUE,$conditionsToClean)) {
            $request->setDestPostcode('');
        }

        return $request;
    }

    /**
     * Is state province required
     *
     * @return bool
     */
    public function isStateProvinceRequired()
    {
        $result = true;
        if (
            (int)$this->_getHelper()->getConfigData('source_rates') !== Summa_Andreani_Model_System_Config_Source_Shipping_Service::ANDREANI_WEBSERVICE_VALUE &&
            !in_array(
                Summa_Andreani_Model_System_Config_Source_Shipping_MatrixratesFilter::REGION_VALUE,
                explode(',',$this->_getHelper()->getConfigData('conditionable_filters'))
            ))
        {
            $result = false;
        }
        return $result;
    }

    /**
     * Check if city option required
     *
     * @return boolean
     */
    public function isCityRequired()
    {
        $result = true;
        if (
            (int)$this->_getHelper()->getConfigData('source_rates') !== Summa_Andreani_Model_System_Config_Source_Shipping_Service::ANDREANI_WEBSERVICE_VALUE &&
            !in_array(
                Summa_Andreani_Model_System_Config_Source_Shipping_MatrixratesFilter::CITY_VALUE,
                explode(',',$this->_getHelper()->getConfigData('conditionable_filters'))
            ))
        {
            $result = false;
        }
        return $result;
    }

    /**
     * Determine whether zip-code is required for the country of destination
     *
     * @param string|null $countryId
     * @return bool
     */
    public function isZipCodeRequired($countryId = null)
    {
        $result = true;
        if (
            (int)$this->_getHelper()->getConfigData('source_rates') !== Summa_Andreani_Model_System_Config_Source_Shipping_Service::ANDREANI_WEBSERVICE_VALUE &&
            !in_array(
                Summa_Andreani_Model_System_Config_Source_Shipping_MatrixratesFilter::ZIP_VALUE,
                explode(',',$this->_getHelper()->getConfigData('conditionable_filters'))
            ))
        {
            $result = false;
        }
        return $result;
    }
    /**
     * Function to process $request lookin for free shipping
     * and returns true or false based-on state free ship.
     * @param Mage_Shipping_Model_Rate_Request $request
     *
     * @return Mage_Shipping_Model_Rate_Request
     */
    protected function _processRequestForFreeShipping(Mage_Shipping_Model_Rate_Request &$request)
    {
        // exclude Virtual products price from Package value if pre-configured
        if (!$this->_getHelper()->getConfigData('include_virtual_price') && $request->getAllItems()) {
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

        // Package weight and qty free shipping
        $oldWeight = $request->getPackageWeight();
        $oldQty = $request->getPackageQty();

        if ($this->_getHelper()->getConfigData('allow_free_shipping_promotions') && !$this->_getHelper()->getConfigData('include_free_ship_items')) {
            $request->setPackageWeight($request->getFreeMethodWeight());
            $request->setPackageQty($oldQty - $freeQty);
        }

        $freeShipping=false;

        if (is_numeric($this->getConfigData('free_shipping_threshold')) &&
            $this->getConfigData('free_shipping_threshold')>0 &&
            $request->getPackageValue()>$this->getConfigData('free_shipping_threshold')) {
            $freeShipping=true;
        }
        if ($this->getConfigData('allow_free_shipping_promotions') &&
            ($request->getFreeShipping() === true ||
                $request->getPackageQty() == $this->getFreeBoxes()))
        {
            $freeShipping=true;
        }
        $request->setIsFreeShipping($freeShipping);
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
     * Function to get Shipment Helper Instance
     * @return Summa_Andreani_Helper_Shipments
     */
    protected function _getShipmentsHelper()
    {
        return Mage::helper('summa_andreani/shipments');
    }

    /**
     * Function to get Branch Helper Instance
     * @return Summa_Andreani_Helper_Branch
     */
    protected function _getBranchHelper()
    {
        return Mage::helper('summa_andreani/branch');
    }

    /**
     * Function to Validate Package Weight
     * @param $totals
     *
     * @return int
     */
    protected function _validateWeight($totals)
    {
        if (is_null($this->_limitWeight)) {
            $this->_limitWeight = $this->_getHelper()->getConfigData('limit_weight');
        }
        $weight = $totals->getTotalWeight();
        if ($weight > $this->_limitWeight) {
            $totals->setErrors($this->_getHelper()->__('Weight limit overflow.'));
            $this->_getHelper()->throwException('Weight limit overflow.',$this->getServiceType());
        }
        if (!$weight || $weight < 0 || is_null($weight)) {
            $totals->setErrors($this->_getHelper()->__('Weight lower than 0.'));
            $totals->setTotalWeight(1);
        }
        return $totals;
    }

    /**
     * Function to validate Package Volume
     * @param $totals
     *
     * @return int
     */
    protected function _validateVolume($totals)
    {
        $volume = $totals->getTotalVolume();
        if (!$volume || $volume < 0 || is_null($volume)) {
            $totals->setErrors($this->_getHelper()->__('Volume lower than 0.'));
            $totals->setTotalVolume(1);
        }
        $totals->setTotalVolume(intval($volume));
        return $totals;
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

    /**
     * Function to returns an varien object with totals Weight and Volume from $items
     * @param $items
     *
     * @return int|Varien_Object
     */
    public function getTotalsWVFromItems($items)
    {
        $response = new Varien_Object();
        $response->setTotalWeight(0);
        $response->setTotalVolume(0);

        $response->setTotalHeight(0);
        $response->setTotalWidth(0);
        $response->setTotalLength(0);
        foreach ($items as $item) {

            if ($item->getProductType() === "simple") {
                $height =
                    Mage::getSingleton('catalog/resource_product')->getAttributeRawValue(
                        $item->getProductId(),
                        $this->_getHelper()->getConfigData('attribute_height'),
                        Mage::app()->getStore()
                    );
                $height = ($height)?$height:0;

                $width =
                    Mage::getSingleton('catalog/resource_product')->getAttributeRawValue(
                        $item->getProductId(),
                        $this->_getHelper()->getConfigData('attribute_width'),
                        Mage::app()->getStore()
                    );
                $width = ($width)?$width:0;

                $length =
                    Mage::getSingleton('catalog/resource_product')->getAttributeRawValue(
                        $item->getProductId(),
                        $this->_getHelper()->getConfigData('attribute_length'),
                        Mage::app()->getStore()
                    );
                $length = ($length)?$length:0;

                $weight =
                    Mage::getSingleton('catalog/resource_product')->getAttributeRawValue(
                        $item->getProductId(),
                        $this->_getHelper()->getConfigData('attribute_weight'),
                        Mage::app()->getStore()
                    );
                $weight = ($weight)? $weight : $this->_getHelper()->calculateWeight($item->getProduct());

                $response->setTotalWeight($response->getTotalWeight() + $weight);
                $response->setTotalVolume($response->getTotalVolume() + ($height * $width * $length));

                $response->setTotalHeight($response->getTotalHeight() + $height);
                $response->setTotalWidth($response->getTotalWidth() + $width);
                $response->setTotalLength($response->getTotalLength() + $length);
            }

        }

        $response = $this->_validateWeight($response);
        $response = $this->_validateVolume($response);

        return $response;
    }

    /**
     * Function to returns an varien object with totals Weight and Volume from $params
     * @param $params
     *
     * @return int|Varien_Object
     */
    public function getTotalsWVFromParams($params)
    {
        $response = new Varien_Object();

        $weightUnits = $params['weight_units'];
        $dimensionUnits = $params['dimension_units'];

        $weightMultiplicator = ($weightUnits == 'KILOGRAM')?1000:453.59237;
        $dimensionMultiplicator = ($dimensionUnits == 'CENTIMETER')?1:0.393700787;

        $height = $params['height'];
        $height = $height * $dimensionMultiplicator;

        $width = $params['width'];
        $width = $width * $dimensionMultiplicator;

        $length = $params['length'];
        $length = $length * $dimensionMultiplicator;

        $weight = $params['weight'];
        $weight = $weight * $weightMultiplicator;

        $response->setTotalWeight($weight);
        $response->setTotalVolume($height * $width * $length);

        $response->setTotalHeight($height);
        $response->setTotalWidth($width);
        $response->setTotalLength($length);

        $response = $this->_validateWeight($response);
        $response = $this->_validateVolume($response);

        return $response;
    }
}
