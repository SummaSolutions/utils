<?php

class Summa_Andreani_Block_Shipping_Additional extends Mage_Core_Block_Template
{
    protected function _construct()
    {
        parent::_construct();
    }

    protected function _getBranches($regionId = null)
    {
        return Mage::getModel('summa_andreani/branch')->getBranches($regionId);
    }

    public function getBranches($regionId)
    {
        return $this->_getBranches($regionId);
    }

    public function getRegions()
    {
        $regionsToReturn = array();
        $ids = $this->helper('summa_andreani')->getRegionIds();
        $regions = Mage::getSingleton('directory/country')
            ->loadByCode('AR')
            ->getRegions();

        foreach ($regions as $region) {
            if(in_array($region->getRegionId(), $ids)){
                $regionsToReturn[] = $region;
            }
        }

        return $regionsToReturn;
    }

    public function getJson()
    {
        $branches = $this->_getBranches();

        $ret = array();
        foreach ($branches as $branch) {
            $ret['id_' . $branch->getBranchId()]['name'] = $branch->getDescription();
            $ret['id_' . $branch->getBranchId()]['address'] = $branch->getAddress();
            $ret['id_' . $branch->getBranchId()]['phone'] = $branch->getPhone1();
            $ret['id_' . $branch->getBranchId()]['email'] = $branch->getEmail();
            $ret['id_' . $branch->getBranchId()]['time_attendance'] = $branch->getTimeAttendance();
        }

        return Mage::helper('core')->jsonEncode($ret);
    }

    public function getRegion()
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