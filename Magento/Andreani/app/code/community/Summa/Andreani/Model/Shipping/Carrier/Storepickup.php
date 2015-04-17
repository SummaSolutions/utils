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

    protected $_code = 'andreaniStorepickup';
    protected $_serviceType = 'storepickup';
    protected $_shippingTypeForMatrixrates = 'Storepickup';

    public function isTrackingAvailable()
    {
        return true;
    }

    public function getAllowedMethods()
    {
        return array($this->_code=>$this->getConfigData('name'));
    }

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
            $username   = $this->_getHelper()->getConfigData('username');
            $password   = $this->_getHelper()->getConfigData('password');

            $gatewayUrl = $this->_getHelper()->getConfigData('gateway_storepickup_url');

            $this->_getHelper()->debugging('fetchBranchesDataConnexion:',$this->getServiceType());
            $this->_getHelper()->debugging(array(
                'username' => $username,
                'password' => $password,
                'gatewayStorePickupUrl' => $gatewayUrl,
                'options' => $options
            ),$this->getServiceType());

            $wsse_header = Mage::getModel('summa_andreani/api_soap_header', array('username'=> $username, 'password'=>$password));

            $client = new SoapClient($gatewayUrl, $options);
            $client->__setSoapHeaders(array($wsse_header));

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

            $andreaniResponse = $client->ConsultarSucursales($branchesToGet);

            $this->_getHelper()->debugging('fetchBranchesResponse:',$this->getServiceType());
            $this->_getHelper()->debugging($andreaniResponse,$this->getServiceType());

            return $andreaniResponse;
        } catch (SoapFault $e) {
            $error = libxml_get_last_error();
            $error .= "<BR><BR>";
            $error .= $e;

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

        $wsse_header = Mage::getModel('summa_andreani/api_soap_header', array('username' => $username, 'password' => $password));

        $client = new SoapClient($gatewayUrl, $options);
        $client->__setSoapHeaders(array($wsse_header));

        $insurance = 0;
        if ($this->_getHelper()->getConfigData('apply_insurance_on_shipping_price')) {
            $insurance = $this->_getHelper()->calculateInsurance($request->getPackageValue());
        }

        $totals = $this->getTotalsWV($request->getAllItems());
        $responseWS = array();
        $collection = Mage::getModel('summa_andreani/branch')->getCollection()
            ->addFieldToFilter('postal_code', $request->getDestPostcode());
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

            $responseWS[] = $this->_parseRatesFromWebService($client->CotizarEnvio($collectRatesInfo), $insurance, $branch->getBranchId());

            $this->_getHelper()->debugging('collectRatesByWebServiceResponse:', $this->getServiceType());
            $this->_getHelper()->debugging($responseWS, $this->getServiceType());
        }

        /** @var $rate Varien_Object */
        foreach ($responseWS as $rate) {
            if (!$rate->hasErrors()) {
                /** @var $method Mage_Shipping_Model_Rate_Result_Method */
                $method = Mage::getModel('shipping/rate_result_method');

                $method->setCarrier($this->_code);
                $method->setCarrierTitle($this->_getHelper()->getConfigData('title', $this->getServiceType()));

                $methodCode = $this->_code;
                $methodTitle = $this->_getHelper()->getConfigData('name', $this->getServiceType());

                if (!is_null($rate->getBranch())) {
                    $methodCode .= '_' . $rate->getBranch();
                    $methodTitle .= ' ' . ucfirst(strtolower($collection->getItemByColumnValue('branch_id',$rate->getBranch())->getDescription()));
                }

                $method->setMethod($methodCode);
                $method->setMethodTitle($methodTitle);

                $method->setCost(0);

                $method->setPrice($this->getFinalPriceWithHandlingFee($rate->getPrice()));

                $result->append($method);
            }
        }

        return $result;
    }
}