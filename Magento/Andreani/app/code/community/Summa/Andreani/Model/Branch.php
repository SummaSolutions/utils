<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        26/03/15
 * Time:        15:11
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */
class Summa_Andreani_Model_Branch
    extends Mage_Core_Model_Abstract
{
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'summa_andreani_branch';

    /**
     * Parameter name in event
     *
     * In observe method you can use $observer->getEvent()->getObject() in this case
     *
     * @var string
     */
    protected $_eventObject = 'andreani_branch';

    /**
     * Short String with carriers service
     * @var string
     */
    protected $_serviceType = null;

    const HIGHER_POSTAL_CODE_CABA = 1500;
    const HIGHER_POSTAL_CODE_GBA = 2000;
    /**
     * Internal constructor not depended on params. Can be used for object initialization
     */
    protected function _construct()
    {
        $this->_init('summa_andreani/branch');
    }

    /**
     * Function to return Service Type
     * @return string
     */
    public function getServiceType()
    {
        if (is_null($this->_serviceType)) {
            $this->_serviceType = Mage::getSingleton('summa_andreani/shipping_carrier_storepickup')->getServiceType();
        }
        return $this->_serviceType;
    }

    /**
     * Function to fetch branches from Andreani Web Service
     * Returns true or false depends of result of andreani
     *
     * @return bool
     */
    public function fetchBranches()
    {
        return $this->_persistBranchesResponse(Mage::getSingleton('summa_andreani/shipping_carrier_storepickup')->fetchBranches());
    }

    /**
     * Function to persist Branches fetched from Andreani
     * @param $response
     *
     * @return bool
     * @throws Exception
     */
    protected function _persistBranchesResponse($response)
    {
        if (
            is_object($response) &&
            isset($response->ConsultarSucursalesResult) &&
            isset($response->ConsultarSucursalesResult->ResultadoConsultarSucursales) &&
            is_array($response->ConsultarSucursalesResult->ResultadoConsultarSucursales)
        ) {
            $model = Mage::getSingleton('summa_andreani/branch');

            $this->setBranchesDisabled();

            $indexedArray = $this->_getIndexedCollectionArray($model);

            foreach ($response->ConsultarSucursalesResult->ResultadoConsultarSucursales as $branch) {
                if(isset($indexedArray[$branch->Sucursal])){
                    $indexedArray[$branch->Sucursal]->addData($this->_buildBranchData($branch));
                    $indexedArray[$branch->Sucursal]->save();
                }else{
                    $model->setData($this->_buildBranchData($branch));
                    $model->save();
                }
            }
            return true;
        } else {
            Mage::helper('summa_andreani')->debugging('Unable to retrieve branches',$this->getServiceType());
            return false;
        }
    }

    /**
     * Returns a collection on an indexed array
     * @param $model
     *
     * @return array
     */
    protected function _getIndexedCollectionArray($model)
    {
        $branches = $model->getCollection();
        $indexedArray = array();

        foreach($branches as $branch){
            $indexedArray[$branch->getBranchId()] = $branch;
        }

        return $indexedArray;
    }

    /**
     * Function to parse branch data from Andreani
     * @param $data
     *
     * @return array
     */
    protected function _buildBranchData($data)
    {

        $resultArr = array();
        $addressExploded = explode(',', $data->Direccion);

        $resultArr['description'] = $data->Descripcion;
        $resultArr['branch_id'] = $data->Sucursal;
        $resultArr['address'] = trim($addressExploded[0]);
        $resultArr['time_attendance'] = $data->HoradeTrabajo;
        $resultArr['lat'] = $data->Latitud;
        $resultArr['long'] = $data->Longitud;
        $resultArr['email'] = $data->Mail;
        $resultArr['kind_phone_1'] = $data->TipoTelefono1;
        $resultArr['phone_1'] = $data->Telefono1;
        $resultArr['kind_phone_2'] = $data->TipoTelefono2;
        $resultArr['phone_2'] = $data->Telefono2;
        $resultArr['kind_phone_3'] = $data->TipoTelefono3;
        $resultArr['phone_3'] = $data->Telefono3;
        $resultArr['postal_code'] = trim($addressExploded[1]);
        $resultArr['city'] = trim($addressExploded[2]);
        $resultArr['region'] = trim($addressExploded[3]);

        $postalCodesDisabled = explode(',',Mage::helper('summa_andreani')->getConfigData('postal_codes_disabled',$this->getServiceType()));
        // Only branches with postal code that not in list of disabled postal codes let enabled.
        // In most cases disabled postal codes is used to avoid show stores in order to branches.
        if (!in_array($resultArr['postal_code'],$postalCodesDisabled)){
            $resultArr['enabled'] = true;
        }
        $resultArr['region_id'] = $this->getRegionId(trim($addressExploded[3]), trim($addressExploded[1]));

        return $resultArr;
    }

    /**
     * Function to get All branches and set these disabled
     */
    protected function setBranchesDisabled()
    {
        $model = Mage::getSingleton('summa_andreani/branch');
        $branches = $model->getCollection();
        foreach($branches as $branch){
            $branch->setEnabled(false);
        }
        $branches->save();
    }

    /**
     * Function to found Region based-on Postal code
     * @param $regionToFound
     * @param $postalCode
     *
     * @return mixed
     */
    protected function getRegionId($regionToFound, $postalCode)
    {
        $model = Mage::getSingleton('directory/region');
        $countryCode = 'AR';
        if ($regionToFound == 'BUENOS AIRES') {
            if ($postalCode < self::HIGHER_POSTAL_CODE_CABA) {
                $region = $model->loadByCode('Ciudad Autónoma de Buenos Aires', $countryCode);
                return $region->getId();
            } else {
                if ($postalCode < self::HIGHER_POSTAL_CODE_GBA) {
                    $region = $model->loadByCode('Buenos Aires - GBA', $countryCode);
                    return $region->getId();
                } else {
                    $region = $model->loadByCode('Buenos Aires - Interior', $countryCode);
                    return $region->getId();
                }
            }
        } else {
            $region = $model->loadByCode($regionToFound, $countryCode);
            return $region->getId();
        }
    }

    /**
     * @param null $regionId
     *
     * @return Summa_Andreani_Model_Resource_Branch_Collection
     */
    public function getBranches($regionId = null)
    {
        $collection = Mage::getModel('summa_andreani/branch')->getCollection()
            ->addFieldToFilter('enabled', 1);

        if($regionId){
            if (is_string($regionId)) {
                $collection->addFieldToFilter('region_id', $regionId);
            } elseif (is_array($regionId)) {
                $collection->addFieldToFilter('region_id', array('in' => $regionId));
            }
        }

        return $collection;
    }
}