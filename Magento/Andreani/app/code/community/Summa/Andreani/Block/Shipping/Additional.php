<?php

class Summa_Andreani_Block_Shipping_Additional extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
    }

    protected function _getBranches($provinceId = null)
    {
        return Mage::getModel('summa_andreani/sucursal')->getBranches($provinceId);
    }

    public function getBranches($provinceId)
    {
        return $this->_getBranches($provinceId);
    }

    public function getProvinces()
    {
        $provincias = array();
        $ids = $this->helper('summa_andreani')->getRegionIds();
        $regions = Mage::getSingleton('directory/country')
            ->loadByCode('AR')
            ->getRegions();

        foreach ($regions as $region) {
            if(in_array($region->getRegionId(), $ids)){
                $provincias[] = $region;
            }
        }

        return $provincias;
    }

    public function getJson()
    {
        $branches = $this->_getBranches();

        $ret = array();
        foreach ($branches as $branch) {
            $ret['id_' . $branch->getSucursalId()]['nombre'] = $branch->getDescripcion();
            $ret['id_' . $branch->getSucursalId()]['direccion'] = $branch->getDireccion();
            $ret['id_' . $branch->getSucursalId()]['telefono'] = $branch->getTelefono_1();
            $ret['id_' . $branch->getSucursalId()]['email'] = $branch->getEmail();
            $ret['id_' . $branch->getSucursalId()]['horario_atencion'] = $branch->getHorario();
        }

        return Mage::helper('core')->jsonEncode($ret);
    }

    public function getProvince()
    {
        $model = Mage::getSingleton('directory/region');
        $countryCode = 'AR';
        $selectedRegion = $this->_getAddress()->getRegion();

        return $model->loadByCode($selectedRegion, $countryCode)->getRegionId();
    }

    protected function _getAddress()
    {
        return Mage::getSingleton('checkout/session')->getQuote()->getBillingAddress();
    }

}