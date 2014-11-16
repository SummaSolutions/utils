<?php

/**
 * Summa_Geolocation_Model_Method_Abstract
 *
 * @category Summa
 * @package  Summa_Geolocation
 * @author   Facundo Capua <fcapua@summasolutions.net>
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link     http://www.summasolutions.net/
 */
abstract class Summa_Geolocation_Model_Method_Abstract
    extends Varien_Object
{
    abstract public function getCountry();

    public function getUserIp()
    {
        return Mage::helper('core/http')->getRemoteAddr();
    }
}