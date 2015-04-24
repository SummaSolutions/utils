<?php
/**
 * Created for  Andreani.
 * @author:     mhidalgo@summasolutions.net
 * Date:        22/04/15
 * Time:        15:35
 * @copyright   Copyright (c) 2015 Summa Solutions (http://www.summasolutions.net)
 */

class Summa_Andreani_Helper_Branch
    extends Mage_Core_Helper_Abstract
{

    public function formatBranchInfo(Summa_Andreani_Model_Branch $branch)
    {
        return ucwords(strtolower($branch->getDescription() . ' - ' . $branch->getAddress()));
    }

    public function getBranchesJson()
    {
        $branches = Mage::getModel('summa_andreani/branch')->getBranches();

        $stores = array();

        foreach ($branches as $branch) {
            $stores[$branch->getRegionId()][$branch->getBranchId()] = array(
                'code' => $branch->getBranchId(),
                'name' => $this->formatBranchInfo($branch)
            );
        }
        $json = Mage::helper('core')->jsonEncode($stores);

        return $json;
    }

    public function getRegionIds()
    {
        $branches = Mage::getModel('summa_andreani/branch')->getBranches();

        $ids = array();

        foreach ($branches as $branch) {
            $ids[] = $branch->getRegionId();
        }

        return $ids;
    }

    public function getBranchesByRegionId($regionId)
    {
        return Mage::getModel('summa_andreani/branch')->getBranches()
            ->addFieldToFilter('region_id', $regionId);
    }
}