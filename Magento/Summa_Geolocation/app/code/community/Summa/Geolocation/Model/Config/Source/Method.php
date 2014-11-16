<?php

/**
 * Class Summa_Geolocation_Model_Config_Source_Method
 *
 * Used to determine which methods are allowed for determining users' location based on IP
 *
 * @category Summa
 * @package  Summa_Geolocation
 * @author   Facundo Capua <fcapua@summasolutions.net>
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link     http://www.summasolutions.net/
 */
class Summa_Geolocation_Model_Config_Source_Method
{

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $return = array();
        foreach(Mage::helper('summa_geolocation')->getMethodsAvailable() as $value=>$label){
            $return[] = array('value' => $value, 'label' => $label);
        }

        return $return;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return Mage::helper('summa_geolocation')->getMethodsAvailable();
    }
}