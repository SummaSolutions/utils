<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        19/03/15
 * Time:        09:08
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */

class Summa_Andreani_Model_Shipping_Carrier_Abstract
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
        return $this->_getHelper()->isEnabled($this->_serviceType);
    }

    /**
     * Function to return array with allowed Methods
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
                $contract    = $this->_getHelper()->getConfigData('contract',$this->_serviceType);

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

                    $this->_getHelper()->debugging('doShipmentRequestDataConnexion:',$this->_serviceType);
                    $this->_getHelper()->debugging(array(
                        'username' => $username,
                        'password' => $password,
                        'gatewayUrl' => $gatewayUrl,
                        'options' => $options
                    ),$this->_serviceType);

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

                $this->_getHelper()->debugging('doShipmentRequestShipmentInfo:',$this->_serviceType);
                $this->_getHelper()->debugging($shipmentInfo,$this->_serviceType);

                $andreaniResponse = $client->ConfirmarCompra(array(
                        'compra' => $shipmentInfo
                    )
                );

                $this->_getHelper()->debugging('doShipmentRequestAndreaniResponse:',$this->_serviceType);
                $this->_getHelper()->debugging($andreaniResponse,$this->_serviceType);

                return($andreaniResponse);
            } catch (SoapFault $e) {
                $error = libxml_get_last_error();
                $error .= "<BR><BR>";
                $error .= $e;

                $this->_getHelper()->debugging('doShipmentRequestAndreaniError:',$this->_serviceType);
                $this->_getHelper()->debugging($e->getMessage(),$this->_serviceType);
                $this->_getHelper()->debugging($error,$this->_serviceType);

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

            $this->_getHelper()->debugging('getLinkConstancyDataConnexion:',$this->_serviceType);
            $this->_getHelper()->debugging(array(
                'username' => $username,
                'password' => $password,
                'gatewayUrl' => $gatewayUrl,
                'options' => $options
            ),$this->_serviceType);

            $wsse_header = Mage::getModel('summa_andreani/api_soap_header', array('username'=> $username, 'password'=>$password));
            $client = new SoapClient($gatewayUrl, $options);
            $client->__setSoapHeaders(array($wsse_header));

            $this->_getHelper()->debugging('getLinkConstancyDataSent:',$this->_serviceType);
            $this->_getHelper()->debugging(array(
                'ParamImprimirConstancia' =>array(
                    'NumeroAndreani' =>$tracking
                )
            ),$this->_serviceType);

            $phpresponse = $client->ImprimirConstancia(array(
                'entities' =>array(
                    'ParamImprimirConstancia' =>array(
                        'NumeroAndreani' =>$tracking
                    ))));

            $this->_getHelper()->debugging('getLinkConstancyResponse:',$this->_serviceType);
            $this->_getHelper()->debugging($phpresponse,$this->_serviceType);

            return $phpresponse;
        } catch (SoapFault $e) {
            $error = (libxml_get_last_error());
            $error .= "<BR><BR>";
            $error .= $e;

            $this->_getHelper()->debugging('getLinkConstancyError:',$this->_serviceType);
            $this->_getHelper()->debugging($e->getMessage(),$this->_serviceType);
            $this->_getHelper()->debugging($error,$this->_serviceType);

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
            $this->_getHelper()->debugging('getTrackingDataConnexion:',$this->_serviceType);
            $this->_getHelper()->debugging(array(
                'clientNro' => $clientNro,
                'gatewayUrl' => $gatewayUrl,
                'options' => $options
            ),$this->_serviceType);

            $client = new SoapClient($gatewayUrl, $options);

            $this->_getHelper()->debugging('getTrackingDataSent:',$this->_serviceType);
            $this->_getHelper()->debugging(array(
                'Pieza' => array(
                    'NroPieza'      => '',
                    'NroAndreani'   => $trackings,
                    'CodigoCliente' => $clientNro
                )
            ),$this->_serviceType);

            $response = $client->ObtenerTrazabilidad(array(
                'Pieza' => array(
                    'NroPieza'      => '',
                    'NroAndreani'   => $trackings,
                    'CodigoCliente' => $clientNro
                )
            ));

            $this->_getHelper()->debugging('getTrackingResponse:',$this->_serviceType);
            $this->_getHelper()->debugging($response,$this->_serviceType);
        } catch (SoapFault $e) {
            $response = $e;
            $this->_getHelper()->debugging('getTrackingError:',$this->_serviceType);
            $this->_getHelper()->debugging($e->getMessage(),$this->_serviceType);
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
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setTracking($t);
                $error->setErrorMessage($r);
                $result->append($error);
            }

            foreach ($resultArr as $t => $data) {
                $tracking = Mage::getModel('shipping/tracking_result_status');
                $tracking->setCarrier($this->_code);
                $tracking->setCarrierTitle($this->getConfigData('title'));
                $tracking->setTracking($t);
                $tracking->addData($data);

                $result->append($tracking);
            }
        } else {
            foreach ($trackings as $t) {
                $error = Mage::getModel('shipping/tracking_result_error');
                $error->setCarrier($this->_code);
                $error->setCarrierTitle($this->getConfigData('title'));
                $error->setTracking($t);
                $error->setErrorMessage($errorTitle);
                $result->append($error);

            }
        }
        $this->_result = $result;
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

        $request->setShippingType($this->_shippingTypeForMatrixrates);

        $rateArray = $this->_getRateArrayFromMatrixrates($request);

        foreach ($rateArray as $rate)
        {
            if (!empty($rate) && $rate['price'] >= 0) {
                /** @var $method Mage_Shipping_Model_Rate_Result_Method */
                $method = Mage::getModel('shipping/rate_result_method');

                $method->setCarrier($this->_code);
                $method->setCarrierTitle($this->_getHelper()->getConfigData('title'));

                $method->setMethod($this->_code);

                $method->setMethodTitle(Mage::helper('matrixrate')->__($rate['delivery_type']));

                $method->setCost($rate['cost']);
                $method->setDeliveryType($rate['delivery_type']);

                $method->setPrice($this->getFinalPriceWithHandlingFee($rate['price']));

                $result->append($method);
            }
        }

        return $result;
    }

    /**
     * Function to get Rate Array from MatrixRates
     *
     * @param $request
     *
     * @return array
     */
    protected function _getRateArrayFromMatrixrates($request)
    {
        /** @var Webshopapps_Matrixrate_Model_Mysql4_Carrier_Matrixrate $resourceMatrixrate */
        $resourceMatrixrate = Mage::getResourceModel('matrixrate_shipping/carrier_matrixrate');
        $rate = $resourceMatrixrate->getNewRate($request);
        return $rate;
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
            $this->_getHelper()->throwException('Weight limit overflow.',$this->_serviceType);
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
}
