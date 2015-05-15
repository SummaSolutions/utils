<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        19/03/15
 * Time:        09:24
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */

class Summa_Andreani_Model_Shipping_Carrier_Storepickup
    extends Summa_Andreani_Model_Shipping_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface
{

    /**
     * Carrier's code
     *
     * @var string
     */
    protected $_code = 'andreaniStorepickup';

    /**
     * Short String with carriers service
     * @var string
     */
    protected $_serviceType = 'storepickup';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'summa_andreani_storepickup';
    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'andreani_storepickup';

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
        return array($this->_code=>$this->getConfigData('name'));
    }

    /**
     * Function to fetch Branches
     * @param null $branches
     *
     * @return bool
     */
    public function fetchBranches($branches = null)
    {
        try
        {

            $options = array(
                'soap_version' => SOAP_1_2,
                'exceptions' => true,
                'trace' => 1,
                'wdsl_local_copy' => true
            );
            $username   = $this->_getHelper()->getUsername($this->getServiceType());
            $password   = $this->_getHelper()->getPassword($this->getServiceType());

            $gatewayUrl = $this->_getHelper()->getConfigData('gateway_storepickup_url');

            $this->_getHelper()->debugging('fetchBranchesDataConnexion:',$this->getServiceType());
            $this->_getHelper()->debugging(array(
                'username' => $username,
                'password' => $password,
                'gatewayStorePickupUrl' => $gatewayUrl,
                'options' => $options
            ),$this->getServiceType());

            $wsse_header = Mage::getModel('summa_andreani/api_soap_header', array('username'=> $username, 'password'=>$password));

            $this->dispatchEvent('fetch_branches_init_soap_client_before',array('gatewayUrl' => $gatewayUrl, 'options' => $options, 'wsse_header' => $wsse_header, 'branches' => $branches));
            $client = new SoapClient($gatewayUrl, $options);
            $client->__setSoapHeaders(array($wsse_header));
            $this->dispatchEvent('fetch_branches_init_soap_client_after',array('client' => $client, 'branches' => $branches));

            if (is_null($branches)) {
                $branchesToGet = array(
                    'consulta' => array()
                );
            } else {
                $branchesToGet = array(
                    'consulta' => $branches
                );
            }

            $this->_getHelper()->debugging('fetchBranchesDataSent:',$this->getServiceType());
            $this->_getHelper()->debugging($branchesToGet,$this->getServiceType());

            $this->dispatchEvent('fetch_branches_call_ws_before',array('dataSent' => $branchesToGet, 'branches' => $branches));
            $response = $client->ConsultarSucursales($branchesToGet);
            $this->dispatchEvent('fetch_branches_call_ws_after',array('response' => $response, 'branches' => $branches));

            $this->_getHelper()->debugging('fetchBranchesResponse:',$this->getServiceType());
            $this->_getHelper()->debugging($response,$this->getServiceType());

            return $response;
        } catch (SoapFault $e) {
            $error = libxml_get_last_error();
            $error .= "<BR><BR>";
            $error .= $e;

            $this->dispatchEvent('fetch_branches_call_ws_after',array('response' => $e, 'branches' => $branches));
            $this->_getHelper()->debugging('fetchBranchesException:',$this->getServiceType());
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
        /** @var Mage_Shipping_Model_Rate_Result $result */
        $result = Mage::getModel('shipping/rate_result');

        $request = $this->_processRequestForFreeShipping($request);

        $freeShipping = $request->getIsFreeShipping();

        $collection = $this->_getBranchHelper()->getBranchesByRegionId($request->getDestRegionId());

        if ($freeShipping)
        {
            foreach ($collection as $branch) {
                /** @var $method Mage_Shipping_Model_Rate_Result_Method */
                $method = Mage::getModel('shipping/rate_result_method');
                $method->setCarrier($this->_code);
                $method->setCarrierTitle($this->_getHelper()->__($this->_getHelper()->getConfigData('title', $this->getServiceType())));

                $methodCode = $this->_freeShipping_method;
                $methodTitle = $this->_getHelper()->__($this->_getHelper()->getFreeMethodText($this->getServiceType()));
                $methodCode .= '_' . $branch->getBranchId();
                $methodTitle .= ' ' . ucfirst(strtolower($branch->getDescription()));

                $method->setMethod($methodCode);
                $method->setMethodTitle($methodTitle);

                $method->setPrice('0.00');
                $result->append($method);
            }

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

            $wsse_header = Mage::getModel('summa_andreani/api_soap_header', array('username' => $username, 'password' => $password));
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
            /** @var $branch Summa_Andreani_Model_Branch */
            foreach ($collection as $branch) {

                $collectRatesInfo = array(
                    'cotizacionEnvio' => array(
                        'CPDestino'      => $request->getDestPostcode(),
                        'Cliente'        => $clientNumber,
                        'Contrato'       => $contract,
                        'Peso'           => $totals->getTotalWeight(),
                        'SucursalRetiro' => $branch->getBranchId(), //Required if it's storepickup
                        'ValorDeclarado' => '', // Optional
                        'Volumen'        => $totals->getTotalVolume(),
                    )
                );

                $this->_getHelper()->debugging('collectRatesByWebServiceDataSent:', $this->getServiceType());
                $this->_getHelper()->debugging($collectRatesInfo, $this->getServiceType());

                $this->dispatchEvent('collect_rates_ws_call_ws_before',array('dataSent' => $collectRatesInfo, 'request' => $request));
                $response[] = $this->_parseRatesFromWebService($client->CotizarEnvio($collectRatesInfo), $insurance, $branch->getBranchId());
                $this->dispatchEvent('collect_rates_ws_call_ws_after',array('response' => $response, 'request' => $request));

                $this->_getHelper()->debugging('collectRatesByWebServiceResponse:', $this->getServiceType());
                $this->_getHelper()->debugging($response, $this->getServiceType());
            }

            /** @var $rate Varien_Object */
            foreach ($response as $rate) {
                if (!$rate->hasErrors()) {
                    /** @var $method Mage_Shipping_Model_Rate_Result_Method */
                    $method = Mage::getModel('shipping/rate_result_method');

                    $method->setCarrier($this->_code);
                    $method->setCarrierTitle($this->_getHelper()->__($this->_getHelper()->getConfigData('title', $this->getServiceType())));

                    $methodCode = $this->_code;
                    $methodTitle = $this->_getHelper()->__($this->_getHelper()->getConfigData('name', $this->getServiceType()));

                    if (!is_null($rate->getBranch())) {
                        $methodCode .= '_' . $rate->getBranch();
                        $methodTitle .= ' ' . ucfirst(strtolower($collection->getItemByColumnValue('branch_id',$rate->getBranch())->getDescription()));
                    }

                    $method->setMethod($methodCode);
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
        }catch(Exception $e) {
            $this->dispatchEvent('collect_rates_ws_call_ws_after',array('response' => $e, 'request' => $request));

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
        }


        return $result;
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

        $collection = $this->_getBranchHelper()->getBranchesByRegionId($request->getDestRegionId());

        if ($freeShipping)
        {
            foreach ($collection as $branch) {
                /** @var $method Mage_Shipping_Model_Rate_Result_Method */
                $method = Mage::getModel('shipping/rate_result_method');
                $method->setCarrier($this->_code);
                $method->setCarrierTitle($this->_getHelper()->__($this->_getHelper()->getConfigData('title', $this->getServiceType())));

                $methodCode = $this->_freeShipping_method;
                $methodTitle = $this->_getHelper()->__($this->_getHelper()->getFreeMethodText($this->getServiceType()));
                $methodCode .= '_' . $branch->getBranchId();
                $methodTitle .= ' ' . ucfirst(strtolower($branch->getDescription()));

                $method->setMethod($methodCode);
                $method->setMethodTitle($methodTitle);

                $method->setPrice('0.00');
                $result->append($method);
            }

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
            foreach ($collection as $branch) {
                if (!empty($rate) &&
                    $rate['delivery_type'] == $this->_getHelper()->getConfigData('shipping_type_for_matrixrates',$this->getServiceType()) &&
                    $rate['price'] >= 0) {
                    /** @var $method Mage_Shipping_Model_Rate_Result_Method */
                    $method = Mage::getModel('shipping/rate_result_method');

                    $method->setCarrier($this->_code);
                    $method->setCarrierTitle($this->_getHelper()->__($this->_getHelper()->getConfigData('title', $this->getServiceType())));


                    $methodCode = $this->_code;
                    $methodTitle = $this->_getHelper()->__($this->_getHelper()->getConfigData('name', $this->getServiceType()));
                    $methodCode .= '_' . $branch->getBranchId();
                    $methodTitle .= ' ' . ucfirst(strtolower($branch->getDescription()));

                    $method->setMethod($methodCode);
                    $method->setMethodTitle($methodTitle);

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
}