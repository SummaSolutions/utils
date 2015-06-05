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

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = 'andreaniAbstract';

    /**
     * Free Method config path
     *
     * @var string
     */
    protected $_freeShipping_method = 'free';

    /**
     * Condition name for Matrix Rates
     * @var string
     */
    protected $_default_condition_name = 'package_weight';

    /**
     * Short String with carriers service
     * @var string
     */
    protected $_serviceType = 'global';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefixGlobal = 'summa_andreani';
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'summa_andreani_abstract';
    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'andreani_abstract';

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

        $this->dispatchEvent('collect_rates_before',array('request' => $request));
        if ((int)$this->_getHelper()->getConfigData('source_rates') === Summa_Andreani_Model_System_Config_Source_Shipping_Service::ANDREANI_WEBSERVICE_VALUE) {
            $this->dispatchEvent('collect_rates_ws_before',array('request' => $request));
            $result = $this->_collectRatesByWebService($request);
            $this->dispatchEvent('collect_rates_ws_after',array('request' => $request, 'result' => $result));
        } else {
            $this->dispatchEvent('collect_rates_matrixrates_before',array('request' => $request));
            $result = $this->_collectRatesByMatrixRates($request);
            $this->dispatchEvent('collect_rates_matrixrates_after',array('request' => $request, 'result' => $result));
        }
        $this->dispatchEvent('collect_rates_after',array('request' => $request, 'result' => $result));
        return $result;
    }

    /**
     * Do request to shipment
     * @param Mage_Shipping_Model_Shipment_Request $request
     *
     * @return Varien_Object
     */
    public function requestToShipment(Mage_Shipping_Model_Shipment_Request $request)
    {
        $this->_getHelper()->debugging('requestToShipment:', $this->getServiceType());
        $this->_getHelper()->debugging($request->getData(), $this->getServiceType());

        $packages = $request->getPackages();
        if (!is_array($packages) || !$packages) {
            Mage::throwException(Mage::helper('summa_andreani')->__('No packages for request'));
        }
        $data = array();
        $helper = $this->_getShipmentsHelper();
        $this->dispatchEvent('request_to_shipment_before',array('request' => $request));
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
                if ($result->hasShippingLabelErrors()) {
                    $helper->addTrackingCode($request->getOrderShipment(),$result,$this);
                } else {
                    $data[] = array(
                        'tracking_number' => $result->getTrackingNumber(),
                        'label_content'   => $helper->preparePdf($result->getShippingLabelContent())
                    );
                }
            }
        }
        $response = new Varien_Object(array(
            'info' => $data
        ));

        $this->dispatchEvent('request_to_shipment_after',array('request' => $request, 'response' => $response));
        return $response;
    }


    /**
     * Do return of shipment
     * @deprecated because Andreani don't support shipping label generation for a return
     *             Code mustn't be removed in order to be used when Andreani begins to provide support for shipping label generation and returns.
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
        $this->dispatchEvent('do_shipment_request_before',array('order' => $order, 'itemsToShip' => $itemsToShip, 'params' => $params));
        $result = new Varien_Object();
        if ($this->isShipmentAvailable()) {
            try {
                $contract = $this->_getHelper()->getContract($this->getServiceType());

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

                $this->dispatchEvent('do_shipment_request_init_soap_client_before',array('gatewayUrl' => $gatewayUrl, 'options' => $options, 'wsse_header' => $wsse_header, 'order' => $order, 'itemsToShip' => $itemsToShip, 'params' => $params));
                $client = new SoapClient($gatewayUrl, $options);
                $client->__setSoapHeaders(array($wsse_header));
                $this->dispatchEvent('do_shipment_request_init_soap_client_after',array('client' => $client, 'order' => $order, 'itemsToShip' => $itemsToShip, 'params' => $params));

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
                    'compra' => array(
                        /* Shipping Data */
                        'SucursalRetiro'            => $address->getAndreaniBranchId(), /* Required = Condicional; */
                        'Provincia'                 => $address->getRegion(),
                        'Localidad'                 => $address->getCity(),
                        'CodigoPostalDestino'       => $address->getPostcode(), /* Required = true; */
                        'Calle'                     => $streets[0], /* Required = true; */
                        'Numero'                    => '-', /* Required = true; */
                        'Departamento'              => NULL,
                        'Piso'                      => NULL,

                        /* Recipient Data */
                        'NombreApellido'            => $address->getFirstname() . ' ' . $address->getLastname(), /* Required = true; */
                        'TipoDocumento'             => 'DNI', /* Required = true; */
                        'NumeroDocumento'           => 'xxxxxxxx', /* Required = true; */
                        'NumeroCelular'             => NULL,
                        'NumeroTelefono'            => $address->getTelephone(),
                        'Email'                     => $order->getCustomerEmail(),
                        'NombreApellidoAlternativo' => NULL,

                        /* Delivery Data  */
                        'NumeroTransaccion'         => $order->getIncrementId(),
                        'DetalleProductosEntrega'   => $detailsProductsSend,
                        'DetalleProductosRetiro'    => NULL,
                        'Peso'                      => $totals->getTotalWeight(),
                        'Volumen'                   => $totals->getTotalVolume(), /* Required = Condicional;  */
                        'ValorACobrar'              => NULL, /* Required = Condicional; */
                        'ValorDeclarado'            => NULL, /* Required = Condicional; */

                        /* Billing Data */
                        'Contrato'                  => $contract, /* Required = true; */
                        'SucursalCliente'           => NULL,/* Required = Condicional; */
                        'CategoriaDistancia'        => $order->getRegionId(), /* Required = Condicional; */
                        'CategoriaFacturacion'      => NULL, /* Required = Condicional; */
                        'CategoriaPeso'             => NULL, /* Required = Condicional; */
                        'Tarifa'                    => NULL /* Required = Condicional; */
                    )
                );

                $this->_getHelper()->debugging('doShipmentRequestDataSent:', $this->getServiceType());
                $this->_getHelper()->debugging($shipmentInfo, $this->getServiceType());

                $this->dispatchEvent('do_shipment_request_call_ws_before',array('shipmentInfo' => $shipmentInfo, 'order' => $order, 'items' => $items, 'params' => $params));
                $response = $client->ConfirmarCompra($shipmentInfo);
                $this->dispatchEvent('do_shipment_request_call_ws_after',array('shipmentInfo' => $shipmentInfo, 'response' => $response, 'order' => $order, 'items' => $items, 'params' => $params));

                $this->_getHelper()->debugging('doShipmentRequestResponse:', $this->getServiceType());
                $this->_getHelper()->debugging($response, $this->getServiceType());

                if (!$response->ConfirmarCompraResult || !$response->ConfirmarCompraResult->NumeroAndreani) {
                    $result->setErrors($this->_getHelper()->__('Service unavailable. The service %s with code %s returns unexpected data.', $this->_getHelper()->getConfigData('title', $this->getServiceType()), $this->getCode()));
                } else {
                    $result->setTrackingNumber($response->ConfirmarCompraResult->NumeroAndreani);
                    $constancyResult = $this->getLinkConstancy($result->getTrackingNumber());
                    if ($constancyResult->getConstancyUrl() && !$constancyResult->hasErrors()) {
                        $result->setShippingLabelContent($constancyResult->getConstancyUrl());
                    } else {
                        $result->setShippingLabelContent($this->_getHelper()->__('Receive') . ' ' . $response->ConfirmarCompraResult->Recibo);
                        if ($constancyResult->hasErrors()) {
                            $result->setShippingLabelErrors($constancyResult->getErrors());
                        }
                    }
                }
                $result->setObjectResponse($response);
            } catch (SoapFault $e) {
                $error = libxml_get_last_error();
                $error .= "<BR><BR>";
                $error .= $e;

                $result->setErrors($e->getMessage());

                $this->dispatchEvent('do_shipment_request_soap_error',array('shipmentInfo' => $shipmentInfo, 'response' => $e, 'order' => $order, 'items' => $items, 'params' => $params));

                $this->_getHelper()->debugging('doShipmentRequestError:', $this->getServiceType());
                $this->_getHelper()->debugging($e->getMessage(), $this->getServiceType());
                $this->_getHelper()->debugging($error, $this->getServiceType());
            } catch (Exception $e) {
                $result->setErrors($e->getMessage());

                $this->dispatchEvent('do_shipment_request_error',array('shipmentInfo' => $shipmentInfo, 'response' => $e, 'order' => $order, 'items' => $items, 'params' => $params));

                $this->_getHelper()->debugging('doShipmentRequestError:', $this->getServiceType());
                $this->_getHelper()->debugging($e->getMessage(), $this->getServiceType());
                $this->_getHelper()->debugging($e, $this->getServiceType());
            }
        } else {
            $result->setErrors($this->_getHelper()->__('Service unavailable. You mus\'t set enabled the service %s with code %s', $this->_getHelper()->getConfigData('title', $this->getServiceType()), $this->getCode()));
        }
        $this->dispatchEvent('do_shipment_request_after',array('order' => $order, 'itemsToShip' => $itemsToShip, 'params' => $params, 'result' => $result));
        return $result;
    }

    /**
     * Function to get Link to PDF with constancy needed for e-commerce
     *
     * @param      $tracking
     *
     * @return Varien_Object
     */
    public function getLinkConstancy($tracking)
    {
        $this->dispatchEvent('get_link_constancy_before',array('tracking' => $tracking));
        $result = new Varien_Object();
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
            $this->dispatchEvent('get_link_constancy_init_soap_client_before',array('gatewayUrl' => $gatewayUrl, 'options' => $options, 'wsse_header' => $wsse_header, 'tracking' => $tracking));
            $client = new SoapClient($gatewayUrl, $options);
            $client->__setSoapHeaders(array($wsse_header));
            $this->dispatchEvent('get_link_constancy_init_soap_client_after',array('client' => $client, 'tracking' => $tracking));

            $dataSent = array(
                'entities' => array(
                    'ParamImprimirConstancia' => array(
                        'NumeroAndreani' => $tracking
                    )
                )
            );
            $this->_getHelper()->debugging('getLinkConstancyDataSent:', $this->getServiceType());
            $this->_getHelper()->debugging($dataSent, $this->getServiceType());

            $this->dispatchEvent('get_link_constancy_call_ws_before',array('dataSent' => $dataSent, 'tracking' => $tracking));
            $response = $client->ImprimirConstancia($dataSent);
            $this->dispatchEvent('get_link_constancy_call_ws_after',array('response' => $response, 'tracking' => $tracking));

            $this->_getHelper()->debugging('getLinkConstancyResponse:', $this->getServiceType());
            $this->_getHelper()->debugging($response, $this->getServiceType());

            if (isset($response->ImprimirConstanciaResult)) {
                $result->setConstancyUrl($response->ImprimirConstanciaResult->ResultadoImprimirConstancia->PdfLinkFile);
            }
            $result->setObjectResponse($response);
        } catch (SoapFault $e) {
            $error = (libxml_get_last_error());
            $error .= "<BR><BR>";
            $error .= $e;

            $this->dispatchEvent('get_link_constancy_soap_error',array('response' => $e, 'tracking' => $tracking));

            $this->_getHelper()->debugging('getLinkConstancyError:', $this->getServiceType());
            $this->_getHelper()->debugging($e->getMessage(), $this->getServiceType());
            $this->_getHelper()->debugging($error, $this->getServiceType());

            $result->setErrors($e->getMessage());
        } catch (Exception $e) {
            $result->setErrors($e->getMessage());

            $this->dispatchEvent('get_link_constancy_error',array('response' => $e, 'tracking' => $tracking));

            $this->_getHelper()->debugging('getLinkConstancyError:', $this->getServiceType());
            $this->_getHelper()->debugging($e->getMessage(), $this->getServiceType());
            $this->_getHelper()->debugging($e, $this->getServiceType());
        }
        $this->dispatchEvent('get_link_constancy_after',array('result' => $result, 'tracking' => $tracking));
        return $result;
    }

    /**
     * Function native of Magento on that tracking status return calls to get info
     * @param $tracking
     *
     * @return bool|mixed
     */
    public function getTrackingInfo($tracking)
    {
        $this->dispatchEvent('get_tracking_info_before',array('tracking' => $tracking));
        $result = $this->getTracking($tracking);
        $this->dispatchEvent('get_tracking_info_after',array('result' => $result, 'tracking' => $tracking));

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
     * @param mixed $tracks
     *
     * @return mixed
     */
    public function getTracking($tracks)
    {
        $this->dispatchEvent('get_tracking_before',array('tracks' => $tracks));
        $clientNumber = $this->_getHelper()->getClientNumber($this->getServiceType());
        $gatewayUrl = $this->_getHelper()->getConfigData('gateway_tracking_url');

        $options = $this->_getHelper()->getSoapOptions();

        try {
            $this->_getHelper()->debugging('getTrackingDataConnexion:', $this->getServiceType());
            $this->_getHelper()->debugging(array(
                'clientNro'  => $clientNumber,
                'gatewayUrl' => $gatewayUrl,
                'options'    => $options
            ), $this->getServiceType());

            $this->dispatchEvent('get_tracking_init_soap_client_before',array('gatewayUrl' => $gatewayUrl, 'options' => $options, 'tracks' => $tracks));
            $client = new SoapClient($gatewayUrl, $options);
            $this->dispatchEvent('get_tracking_init_soap_client_after',array('client' => $client, 'tracks' => $tracks));

            $dataSent = array(
                'Pieza' => array(
                    'NroPieza'      => '',
                    'NroAndreani'   => $tracks,
                    'CodigoCliente' => $clientNumber
                )
            );
            $this->_getHelper()->debugging('getTrackingDataSent:', $this->getServiceType());
            $this->_getHelper()->debugging($dataSent, $this->getServiceType());

            $this->dispatchEvent('get_tracking_call_ws_before',array('dataSent' => $dataSent, 'tracks' => $tracks));
            $response = $client->ObtenerTrazabilidad($dataSent);
            $this->dispatchEvent('get_tracking_call_ws_after',array('response' => $response, 'tracks' => $tracks));

            $this->_getHelper()->debugging('getTrackingResponse:', $this->getServiceType());
            $this->_getHelper()->debugging($response, $this->getServiceType());
        } catch (SoapFault $e) {
            $this->dispatchEvent('get_tracking_soap_error',array('response' => $e, 'tracks' => $tracks));
            $response = $e;
            $this->_getHelper()->debugging('getTrackingError:', $this->getServiceType());
            $this->_getHelper()->debugging($e->getMessage(), $this->getServiceType());
        } catch (Exception $e) {
            $response = $e;

            $this->dispatchEvent('get_tracking_error',array('response' => $e, 'tracks' => $tracks));

            $this->_getHelper()->debugging('getTrackingError:', $this->getServiceType());
            $this->_getHelper()->debugging($e->getMessage(), $this->getServiceType());
            $this->_getHelper()->debugging($e, $this->getServiceType());
        }

        $result = $this->_parseXmlTrackingResponse($tracks, $response);
        $this->dispatchEvent('get_tracking_after',array('result' => $result, 'tracks' => $tracks));

        return $result;
    }

    /**
     * Function to parse XML response from Andreani Web Services
     * And Update status of shipment if it's needed
     * @param $tracks
     * @param $response
     *
     * @return Mage_Shipping_Model_Tracking_Result
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

        /** @var Mage_Shipping_Model_Tracking_Result $result */
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
        return $result;
    }

    /**
     * Function to cancel Shipment request to andreani
     *
     * @param $tracking
     *
     * @return bool
     */
    public function cancelShipmentRequest($tracking)
    {
        $result = new Varien_Object();
        $this->dispatchEvent('cancel_shipment_request_before',array('tracking' => $tracking));
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

            $this->dispatchEvent('cancel_shipment_request_init_soap_client_before',array('gatewayUrl' => $gatewayUrl, 'options' => $options, 'wsse_header' => $wsse_header, 'tracking' => $tracking));
            $client = new SoapClient($gatewayUrl, $options);
            $client->__setSoapHeaders(array($wsse_header));
            $this->dispatchEvent('cancel_shipment_request_init_soap_client_after',array('client' => $client, 'tracking' => $tracking));

            $cancelShipmentInfo = array(
                'envios' => array(
                    'ParamAnularEnvios' => array(
                        'NumeroAndreani' => $tracking
                    )
                )
            );
            $this->_getHelper()->debugging('cancelShipmentRequestDataSent:', $this->getServiceType());
            $this->_getHelper()->debugging($cancelShipmentInfo, $this->getServiceType());

            $this->dispatchEvent('cancel_shipment_request_call_ws_before',array('dataSent' => $cancelShipmentInfo, 'tracking' => $tracking));
            $response = $client->AnularEnvios($cancelShipmentInfo);
            $this->dispatchEvent('cancel_shipment_request_call_ws_after',array('response' => $response, 'tracking' => $tracking));

            $this->_getHelper()->debugging('cancelShipmentRequestResponse:', $this->getServiceType());
            $this->_getHelper()->debugging($response, $this->getServiceType());

            if (isset($response->AnularEnviosResult)) {
                $result->setCanceledShipment(true);
            } else {
                $result->setCanceledShipment(false);
            }

            $result->setObjectResponse($response);

        } catch (SoapFault $e) {
            $this->dispatchEvent('cancel_shipment_request_soap_error',array('response' => $e, 'tracking' => $tracking));
            $error = libxml_get_last_error();
            $error .= "<BR><BR>";
            $error .= $e;

            $this->_getHelper()->debugging('cancelShipmentRequestError:', $this->getServiceType());
            $this->_getHelper()->debugging($e->getMessage(), $this->getServiceType());
            $this->_getHelper()->debugging($error, $this->getServiceType());

            $result->setErrors($e->getMessage());
        } catch (Exception $e) {
            $result->setErrors($e->getMessage());

            $this->dispatchEvent('cancel_shipment_request_error',array('response' => $e, 'tracking' => $tracking));

            $this->_getHelper()->debugging('cancelShipmentRequestError:', $this->getServiceType());
            $this->_getHelper()->debugging($e->getMessage(), $this->getServiceType());
            $this->_getHelper()->debugging($e, $this->getServiceType());
        }

        $this->dispatchEvent('cancel_shipment_request_after',array('result' => $result, 'tracking' => $tracking));
        return $result;
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
            $method->setCarrierTitle($this->_getHelper()->__($this->_getHelper()->getConfigData('title', $this->getServiceType())));
            $method->setMethod($this->_freeShipping_method);
            $method->setMethodTitle($this->_getHelper()->__($this->_getHelper()->getFreeMethodText($this->getServiceType())));
            $method->setPrice('0.00');
            $result->append($method);

            if ($this->getConfigData('show_only_free')) {
                return $result;
            }
        }
        try {
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

            $this->dispatchEvent('collect_rates_ws_init_soap_client_before',array('gatewayUrl' => $gatewayUrl, 'options' => $options, 'wsse_header' => $wsse_header, 'request' => $request));
            $client = new SoapClient($gatewayUrl, $options);
            $client->__setSoapHeaders(array($wsse_header));
            $this->dispatchEvent('collect_rates_ws_init_soap_client_after',array('client' => $client, 'request' => $request));

            $insurance = 0;
            if ($this->_getHelper()->getConfigData('apply_insurance_on_shipping_price')) {
                $insurance = $this->_getHelper()->calculateInsurance($request->getPackageValue());
            }
            $totals = $this->getTotalsWVFromItems($request->getAllItems());
            $response = array();
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

            $this->dispatchEvent('collect_rates_ws_call_ws_before',array('dataSent' => $collectRatesInfo, 'request' => $request));
            $response[] = $this->_parseRatesFromWebService($client->CotizarEnvio($collectRatesInfo), $insurance);
            $this->dispatchEvent('collect_rates_ws_call_ws_after',array('response' => $response, 'request' => $request));

            $this->_getHelper()->debugging('collectRatesByWebServiceResponse:', $this->getServiceType());
            $this->_getHelper()->debugging($response, $this->getServiceType());

            /** @var $rate Varien_Object */
            foreach ($response as $rate) {
                if (!$rate->hasErrors()) {
                    /** @var $method Mage_Shipping_Model_Rate_Result_Method */
                    $method = Mage::getModel('shipping/rate_result_method');

                    $method->setCarrier($this->_code);
                    $method->setCarrierTitle($this->_getHelper()->__($this->_getHelper()->getConfigData('title', $this->getServiceType())));

                    $method->setMethod($this->_code);

                    $methodTitle = $this->_getHelper()->__($this->_getHelper()->getConfigData('name', $this->getServiceType()));

                    $method->setMethodTitle($methodTitle);

                    $method->setCost(0);

                    $method->setPrice($this->getFinalPriceWithHandlingFee($rate->getPrice()));

                    $result->append($method);
                } elseif($this->_getHelper()->getConfigData('showmethod',$this->getServiceType())) {
                    $error = Mage::getModel('shipping/rate_result_error');
                    $error->setCarrier($this->_code);
                    $error->setCarrierTitle($this->_getHelper()->__($this->_getHelper()->getConfigData('title', $this->getServiceType())));
                    $error->setErrorMessage($this->_getHelper()->__('Andreani is not available. %s',$rate->getErrors()));
                    $result = $error;
                }
            }
        } catch (SoapFault $e) {
            $this->dispatchEvent('collect_rates_ws_soap_error',array('response' => $e, 'request' => $request));

            $error = libxml_get_last_error();
            $error .= "<BR><BR>";
            $error .= $e;

            $this->_getHelper()->debugging('collectRatesByWebServiceError:', $this->getServiceType());
            $this->_getHelper()->debugging($e->getMessage(), $this->getServiceType());
            $this->_getHelper()->debugging($error, $this->getServiceType());

            if($this->_getHelper()->getConfigData('showmethod',$this->getServiceType())) {
                $error = Mage::getModel('shipping/rate_result_error');
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->_getHelper()->__($this->_getHelper()->getConfigData('title', $this->getServiceType())));
                $error->setErrorMessage($this->_getHelper()->__('Andreani is not available for this request.'));
                $result = $error;
            }
        } catch (Exception $e) {
            $this->dispatchEvent('collect_rates_ws_error',array('response' => $e, 'request' => $request));

            $this->_getHelper()->debugging('collectRatesByWebServiceError:', $this->getServiceType());
            $this->_getHelper()->debugging($e->getMessage(), $this->getServiceType());
            $this->_getHelper()->debugging($e, $this->getServiceType());

            if($this->_getHelper()->getConfigData('showmethod',$this->getServiceType())) {
                $error = Mage::getModel('shipping/rate_result_error');
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->_getHelper()->__($this->_getHelper()->getConfigData('title', $this->getServiceType())));
                $error->setErrorMessage($this->_getHelper()->__('Andreani is not available for this request.'));
                $result = $error;
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
            $method->setCarrierTitle($this->_getHelper()->__($this->_getHelper()->getConfigData('title', $this->getServiceType())));
            $method->setMethod($this->_freeShipping_method);
            $method->setMethodTitle($this->_getHelper()->__($this->_getHelper()->getFreeMethodText($this->getServiceType())));
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

        $vat = 0;
        if ($this->_getHelper()->getConfigData('add_vat_to_rates')) {
            $vat = $this->_getHelper()->calculateVAT($request->getPackageValue());
        }

        foreach ($rateArray as $rate) {
            if (!empty($rate) && $rate['delivery_type'] == $this->_getHelper()->getConfigData('shipping_type_for_matrixrates',$this->getServiceType()) && $rate['price'] >= 0) {
                /** @var $method Mage_Shipping_Model_Rate_Result_Method */
                $method = Mage::getModel('shipping/rate_result_method');

                $method->setCarrier($this->_code);
                $method->setCarrierTitle($this->_getHelper()->__($this->_getHelper()->getConfigData('title', $this->getServiceType())));

                $method->setMethod($this->_code);

                $method->setMethodTitle($this->_getHelper()->__($this->_getHelper()->getConfigData('name', $this->getServiceType())));

                $method->setCost($rate['cost']);
                $method->setDeliveryType($rate['delivery_type']);

                $price = $rate['price'];

                if ($insurance) {
                    $price += $insurance;
                }

                if ($vat) {
                    $price += $vat;
                }

                $method->setPrice($this->getFinalPriceWithHandlingFee($price));

                $result->append($method);
            }
        }

        if (!$result->getAllRates() && $this->_getHelper()->getConfigData('showmethod',$this->getServiceType())) {
            $error = Mage::getModel('shipping/rate_result_error');
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->_getHelper()->__($this->_getHelper()->getConfigData('title', $this->getServiceType())));
            $error->setErrorMessage($this->_getHelper()->__('Andreani is not available for this request.'));
            $result = $error;
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
        if (!$this->_getHelper()->getConfigData('include_virtual_price',$this->getServiceType()) && $request->getAllItems()) {
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

        if ($this->_getHelper()->getConfigData('allow_free_shipping_promotions',$this->getServiceType()) && !$this->_getHelper()->getConfigData('include_free_ship_items',$this->getServiceType())) {
            $request->setPackageWeight($request->getFreeMethodWeight());
            $request->setPackageQty($oldQty - $freeQty);
        }

        $freeShipping=false;

        if (is_numeric($this->getConfigData('free_shipping_threshold',$this->getServiceType())) &&
            $this->getConfigData('free_shipping_threshold',$this->getServiceType())>0 &&
            $request->getPackageValue()>$this->getConfigData('free_shipping_threshold',$this->getServiceType())) {
            $freeShipping=true;
        }
        if ($this->getConfigData('allow_free_shipping_promotions',$this->getServiceType()) &&
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
        $maximumLimitWeight = $this->_getHelper()->getConfigData('maximum_limit_weight');
        $minimumLimitWeight = $this->_getHelper()->getConfigData('minimum_limit_weight');
        $weight = $totals->getTotalWeight();
        if ($weight > $maximumLimitWeight) {
            $totals->setErrors($this->_getHelper()->__('Maximum Weight limit overflow.'));
            $this->_getHelper()->throwException('Weight limit overflow.',$this->getServiceType());
        }
        if (!$weight || $weight < 0 || is_null($weight) || $weight < $minimumLimitWeight) {
            $totals->setErrors($this->_getHelper()->__('Minimum Weight limit overflow.'));
            switch ($this->_getHelper()->getConfigData('minimum_limit_action'))
            {
                case Summa_Andreani_Model_System_Config_Source_Shipping_MinimumSettings::SET_MINIMUM_LIMIT:
                    $totals->setTotalWeight($minimumLimitWeight);
                    break;
                case Summa_Andreani_Model_System_Config_Source_Shipping_MinimumSettings::SET_CUSTOM:
                    $totals->setTotalWeight($this->_getHelper()->getConfigData('minimum_weight_custom'));
                    break;
                case Summa_Andreani_Model_System_Config_Source_Shipping_MinimumSettings::SET_EXCEPTION:
                    $this->_getHelper()->throwException('Minimum Weight limit overflow.',$this->getServiceType());
                    break;
            }
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
        $minimumLimitVolume = $this->_getHelper()->getConfigData('minimum_limit_volume');
        $volume = $totals->getTotalVolume();
        if (!$volume || $volume < 0 || is_null($volume) || $volume < $minimumLimitVolume) {
            $totals->setErrors($this->_getHelper()->__('Minimum Volume limit overflow.'));
            $totals->setTotalVolume($minimumLimitVolume);
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
            if ($item instanceof Mage_Sales_Model_Order_Shipment_Item)
            {
                $item = $item->getOrderItem();
                if ($item->getHasChildren()) {
                    $result = $this->getTotalsWVFromItems($item->getChildrenItems());

                    $response->setTotalWeight($response->getTotalWeight() + $result->getTotalWeight());
                    $response->setTotalVolume($response->getTotalVolume() + $result->getTotalVolume());

                    $response->setTotalHeight($response->getTotalHeight() + $result->getTotalHeight());
                    $response->setTotalWidth($response->getTotalWidth() + $result->getTotalWidth());
                    $response->setTotalLength($response->getTotalLength() + $result->getTotalLength());
                }
            }
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

                $qty = ($parent = $item->getParentItem()) ?
                    $parent->getQtyShipped():
                    $item->getQtyShipped();

                $response->setTotalWeight($response->getTotalWeight() + ($weight * $qty));
                $response->setTotalVolume($response->getTotalVolume() + (($height * $width * $length) * $qty));

                $response->setTotalHeight($response->getTotalHeight() + ($height * $qty));
                $response->setTotalWidth($response->getTotalWidth() + ($width * $qty));
                $response->setTotalLength($response->getTotalLength() + ($length * $qty));
            }

        }

        // Validate Weight
        $response = $this->_validateWeight($response);
        if ($response->hasErrors())
        {
            $this->_getHelper()->debugging($response->getErrors(),$this->getServiceType());
            $response->unsetErrors();
        }
        // Validate Volume
        $response = $this->_validateVolume($response);
        if ($response->hasErrors())
        {
            $this->_getHelper()->debugging($response->getErrors(),$this->getServiceType());
            $response->unsetErrors();
        }

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

        $weightMultiplicator = ($weightUnits == Zend_Measure_Weight::KILOGRAM)?1000:453.59237;
        $dimensionMultiplicator = ($dimensionUnits == Zend_Measure_Length::CENTIMETER)?1:0.393700787;

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


        // Validate Weight
        $response = $this->_validateWeight($response);
        if ($response->hasErrors())
        {
            $this->_getHelper()->debugging($response->getErrors(),$this->getServiceType());
            $response->unsetErrors();
        }
        // Validate Volume
        $response = $this->_validateVolume($response);
        if ($response->hasErrors())
        {
            $this->_getHelper()->debugging($response->getErrors(),$this->getServiceType());
            $response->unsetErrors();
        }

        return $response;
    }

    /**
     * Function to dispatch events related to Andreani
     * @param       $event
     * @param array $params
     */
    public function dispatchEvent($event,$params = array())
    {
        $eventParams = array(
            'data_object' => $this,
            $this->_eventObject => $this
        );

        $eventParams = array_merge($eventParams,$params);

        // dispatch global event
        Mage::dispatchEvent($this->_eventPrefixGlobal . '_' . $event,$eventParams);
        // dispatch event
        Mage::dispatchEvent($this->_eventPrefix . '_' . $event,$eventParams);
    }
}
