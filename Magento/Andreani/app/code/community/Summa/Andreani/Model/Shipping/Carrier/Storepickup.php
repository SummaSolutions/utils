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

    protected $_code = 'andreani_storepickup';
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
}