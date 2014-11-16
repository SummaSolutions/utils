<?php

/**
 * Class Summa_Geolocation_Model_Method_Php
 *
 * @category Summa
 * @package  Summa_Geolocation
 * @author   Facundo Capua <fcapua@summasolutions.net>
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link     http://www.summasolutions.net/
 */
class Summa_Geolocation_Model_Method_Php
    extends Summa_Geolocation_Model_Method_Abstract
{
    public function getCountry()
    {
        if(!function_exists('geoip_country_code_by_name')){
            throw new Mage_Exception('The GeoIP extension for PHP is not available.');
        }

        $ip = $this->getUserIp();
        $countryCode = geoip_country_code_by_name($ip);

        return $countryCode;
    }
}