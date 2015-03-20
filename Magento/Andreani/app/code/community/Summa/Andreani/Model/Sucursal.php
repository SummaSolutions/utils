<?php

class Summa_Andreani_Model_Sucursal
    extends Mage_Core_Model_Abstract
{
    protected function _construct()
    {
        $this->_init('summa_andreani/sucursal');
    }

    public function fetchBranches()
    {
        try
        {
            $options = array(
                'soap_version' => SOAP_1_2,
                'exceptions' => true,
                'trace' => 1,
                'wdsl_local_copy' => true
            );

            $username   = Mage::getStoreConfig('andreani_config/sucursal_tab/username');
            $password   = Mage::getStoreConfig('andreani_config/sucursal_tab/password');
            $gatewayUrl = Mage::getStoreConfig('andreani_config/sucursal_tab/gateway_url');
            $debug_mode = Mage::getStoreConfig('andreani_config/sucursal_tab/debug_mode');

            $wsse_header = Mage::getModel('summa_andreani/api_soap_header', array('username'=> $username, 'password'=>$password));

            $client = new SoapClient($gatewayUrl, $options);
            $client->__setSoapHeaders(array($wsse_header));

            $andreaniResponse = $client->ConsultarSucursales(array(
                'consulta' => array()
            ));

            if ($debug_mode) {
                Mage::log('andreaniResponse:', null, 'andreani.log');
                Mage::log($andreaniResponse, null, 'andreani.log');
            }
            $this->_persistBranchesResponse($andreaniResponse);
        } catch (SoapFault $e) {
            $error = libxml_get_last_error();
            $error .= "<BR><BR>";
            $error .= $e;

            if ($debug_mode) {
                Mage::log('Exception:', null, 'andreani.log');
                Mage::log($e->getMessage(), null, 'andreani.log');
                Mage::log($error, null, 'andreani.log');
            }

            return false;
        }
    }

    protected function _persistBranchesResponse($response)
    {
        $errorTitle = Mage::helper('summa_andreani')->__('Unable to retrieve tracking');
        $model = Mage::getSingleton('summa_andreani/sucursal');

        $this->setBranchesDisabled();

        $indexedArray = $this->_getIndexedCollectionArray($model);

        if (is_object($response)) {
            if(isset($response->ConsultarSucursalesResult))
            {
                if(isset($response->ConsultarSucursalesResult->ResultadoConsultarSucursales) && is_array($response->ConsultarSucursalesResult->ResultadoConsultarSucursales))
                {
                    foreach ($response->ConsultarSucursalesResult->ResultadoConsultarSucursales as $sucursal) {
                        if(isset($indexedArray[$sucursal->Sucursal])){
                            $indexedArray[$sucursal->Sucursal]->addData($this->_buildBranchData($sucursal));
                            $indexedArray[$sucursal->Sucursal]->save();
                        }else{
                            $model->setData($this->_buildBranchData($sucursal));
                            $model->save();
                        }
                    }
                }else{
                    Mage::log($errorTitle, null, 'andreani.log');
                }
            }else{
                Mage::log($errorTitle, null, 'andreani.log');
            }
        }else{
            Mage::log($errorTitle, null, 'andreani.log');
        }
    }

    protected function _getIndexedCollectionArray($model)
    {
        $branches = $model->getCollection();
        $indexedArray = array();

        foreach($branches as $branch){
            $indexedArray[$branch->getSucursalId()] = $branch;
        }

        return $indexedArray;
    }

    protected function _buildBranchData($data)
    {

        $resultArr = array();
        $addressExploded = explode(',', $data->Direccion);

        $resultArr['descripcion'] = $data->Descripcion;
        $resultArr['sucursal_id'] = $data->Sucursal;
        $resultArr['direccion'] = trim($addressExploded[0]);
        $resultArr['horario'] = $data->HoradeTrabajo;
        $resultArr['latitud'] = $data->Latitud;
        $resultArr['longitud'] = $data->Longitud;
        $resultArr['email'] = $data->Mail;
        $resultArr['tipo_telefono_1'] = $data->TipoTelefono1;
        $resultArr['telefono_1'] = $data->Telefono1;
        $resultArr['tipo_telefono_2'] = $data->TipoTelefono2;
        $resultArr['telefono_2'] = $data->Telefono2;
        $resultArr['tipo_telefono_3'] = $data->TipoTelefono3;
        $resultArr['telefono_3'] = $data->Telefono3;
        $resultArr['codigo_postal'] = trim($addressExploded[1]);
        $resultArr['localidad'] = trim($addressExploded[2]);
        $resultArr['provincia'] = trim($addressExploded[3]);
        //habilito solo las sucursales que no sean Cordoba - Villa Dolores, esta no es una sucursal sino un deposito
        if ($resultArr['codigo_postal'] != 5870){
            $resultArr['enabled'] = true;
        }
        $resultArr['region_id'] = $this->getRegionId(trim($addressExploded[3]), trim($addressExploded[1]));

        return $resultArr;
    }

    protected function setBranchesDisabled()
    {
        $model = Mage::getSingleton('summa_andreani/sucursal');
        $sucursales = $model->getCollection();
        foreach($sucursales as $sucursal){
            $sucursal->_data['enabled'] = false;
        }
        $sucursales->save();
    }

    protected function getRegionId($provincia, $codigoPostal)
    {
        $model = Mage::getSingleton('directory/region');
        $countryCode = 'AR';
        if ($provincia == 'BUENOS AIRES') {
            if ($codigoPostal < 1500) {
                $region = $model->loadByCode('Ciudad Autónoma de Buenos Aires', $countryCode);
                return $region->getId();
            } else {
               if ($codigoPostal < 2000) {
                   $region = $model->loadByCode('Buenos Aires - GBA', $countryCode);
                   return $region->getId();
               } else {
                   $region = $model->loadByCode('Buenos Aires - Interior', $countryCode);
                   return $region->getId();
               }
            }
        } else {
            $region = $model->loadByCode($provincia, $countryCode);
            return $region->getId();
        }
    }

    public function getBranches($provinceId = null)
    {
        $collection = Mage::getModel('summa_andreani/sucursal')->getCollection()
            ->addFieldToFilter('enabled', 1);

        if($provinceId){
            $collection->addFieldToFilter('region_id', $provinceId);
        }

        return $collection;
    }
}
