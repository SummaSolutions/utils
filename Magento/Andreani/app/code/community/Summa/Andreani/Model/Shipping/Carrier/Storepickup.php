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
        $allowedMethods = array();
        $collection = Mage::getModel('summa_andreani/branch')->getBranches();
        foreach ($collection as $branch) {
            $methodCode = $this->_code;
            $methodTitle = $this->_getHelper()->__($this->_getHelper()->getConfigData('name', $this->getServiceType()));
            $methodCode .= '_' . $branch->getBranchId();
            $methodTitle .= ' ' . ucfirst(strtolower($branch->getDescription()));
            $allowedMethods[$methodCode] = $methodTitle;
        }
        return $allowedMethods;
    }

    /**
     * Function to fetch Branches
     * @param null $branches
     *
     * @return bool
     */
    public function fetchBranches($branches = null)
    {
        if (!is_null($branches)) {
            $branches = new Varien_Object(array('branches' => $branches));
        }
        $result = new Varien_Object();
        try
        {
            $connexionData = new Varien_Object(array(
                'username'      => $this->_getHelper()->getUsername($this->getServiceType()),
                'password'      => $this->_getHelper()->getPassword($this->getServiceType()),
                'gateway_url'   => $this->_getHelper()->getConfigData('gateway_storepickup_url'),
                'options'       => $this->_getHelper()->getSoapOptions()
            ));
            $this->_getHelper()->debugging('fetchBranchesDataConnexion:',$this->getServiceType());
            $this->_getHelper()->debugging($connexionData->getData(),$this->getServiceType());

            $wsse_header = $this->_getHelper()->getWsseHeader($connexionData->getUsername(),$connexionData->getPassword());

            $this->dispatchEvent('fetch_branches_init_soap_client_before',array('gateway_url' => $connexionData->getGatewayUrl(), 'options' => $connexionData->getOptions(), 'wsse_header' => $wsse_header, 'branches' => $branches));
            $client = new SoapClient($connexionData->getGatewayUrl(), $connexionData->getOptions());
            $client->__setSoapHeaders(array($wsse_header));
            $this->dispatchEvent('fetch_branches_init_soap_client_after',array('client' => $client, 'branches' => $branches));

            if (is_null($branches)) {
                $branchesToGet = new Varien_Object(
                    array(
                        'consulta' => array()
                    )
                );
            } else {
                $branchesToGet = new Varien_Object(
                    array(
                        'consulta' => $branches->getBranches()
                    )
                );
            }

            $this->_getHelper()->debugging('fetchBranchesDataSent:',$this->getServiceType());
            $this->_getHelper()->debugging($branchesToGet->getData(),$this->getServiceType());

            $this->dispatchEvent('fetch_branches_call_ws_before',array('data_sent' => $branchesToGet, 'branches' => $branches));
            $response = $client->ConsultarSucursales($branchesToGet->getData());
            $this->dispatchEvent('fetch_branches_call_ws_after',array('response' => $response, 'branches' => $branches));

            $this->_getHelper()->debugging('fetchBranchesResponse:',$this->getServiceType());
            $this->_getHelper()->debugging($response,$this->getServiceType());
            if (
                is_object($response) &&
                isset($response->ConsultarSucursalesResult) &&
                isset($response->ConsultarSucursalesResult->ResultadoConsultarSucursales) &&
                is_array($response->ConsultarSucursalesResult->ResultadoConsultarSucursales)
            ) {
                $result->setFetchedBranches(true);
            } else {
                $result->setFetchedBranches(false);
            }
            $result->setObjectResponse($response);
        } catch (SoapFault $e) {
            $error = libxml_get_last_error();
            $error .= "<BR><BR>";
            $error .= $e;

            $result->setErrors($e->getMessage());
            $result->setFetchedBranches(false);
            $this->dispatchEvent('fetch_branches_soap_error',array('response' => $e, 'branches' => $branches));
            $this->_getHelper()->debugging('fetchBranchesError:',$this->getServiceType());
            $this->_getHelper()->debugging($e->getMessage(),$this->getServiceType());
            $this->_getHelper()->debugging($error,$this->getServiceType());

        } catch (Exception $e) {

            $result->setErrors($e->getMessage());
            $result->setFetchedBranches(false);
            $this->dispatchEvent('fetch_branches_error',array('response' => $e, 'branches' => $branches));
            $this->_getHelper()->debugging('fetchBranchesError:', $this->getServiceType());
            $this->_getHelper()->debugging($e->getMessage(), $this->getServiceType());
            $this->_getHelper()->debugging($e, $this->getServiceType());
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

                $methodCode = $this->_code;
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
            $connexionData = new Varien_Object(array(
                'client_number' => $this->_getHelper()->getClientNumber($this->getServiceType()),
                'contract'      => $this->_getHelper()->getContract($this->getServiceType()),
                'username'      => $this->_getHelper()->getUsername($this->getServiceType()),
                'password'      => $this->_getHelper()->getPassword($this->getServiceType()),
                'gateway_url'   => $this->_getHelper()->getConfigData('gateway_rates_url'),
                'options'       => $this->_getHelper()->getSoapOptions()
            ));

            $this->_getHelper()->debugging('collectRatesByWebServiceDataConnexion:', $this->getServiceType());
            $this->_getHelper()->debugging($connexionData->getData(), $this->getServiceType());

            $wsse_header = $this->_getHelper()->getWsseHeader($connexionData->getUsername(),$connexionData->getPassword());

            $this->dispatchEvent('collect_rates_ws_init_soap_client_before',array('gateway_url' => $connexionData->getGatewayUrl(), 'options' => $connexionData->getOptions(), 'wsse_header' => $wsse_header, 'request' => $request));
            $client = new SoapClient($connexionData->getGatewayUrl(), $connexionData->getOptions());
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

                $collectRatesInfo = new Varien_Object(
                    array(
                        'cotizacionEnvio' => array(
                            'CPDestino'      => $branch->getPostalCode(),
                            'Cliente'        => $connexionData->getClientNumber(),
                            'Contrato'       => $connexionData->getContract(),
                            'Peso'           => $totals->getTotalWeight(),
                            'SucursalRetiro' => $branch->getBranchId(), //Required if it's storepickup
                            'ValorDeclarado' => '', // Optional
                            'Volumen'        => $totals->getTotalVolume(),
                        )
                    )
                );

                $this->_getHelper()->debugging('collectRatesByWebServiceDataSent:', $this->getServiceType());
                $this->_getHelper()->debugging($collectRatesInfo->getData(), $this->getServiceType());

                $this->dispatchEvent('collect_rates_ws_call_ws_before',array('data_sent' => $collectRatesInfo, 'request' => $request));
                $response[] = $this->_parseRatesFromWebService($client->CotizarEnvio($collectRatesInfo->getData()), $insurance, $branch->getBranchId());
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
        } catch(SoapFault $e) {
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

        $vat = 0;
        if ($this->_getHelper()->getConfigData('add_vat_to_rates')) {
            $vat = $this->_getHelper()->calculateVAT($request->getPackageValue());
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

                    if ($vat) {
                        $price += $vat;
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