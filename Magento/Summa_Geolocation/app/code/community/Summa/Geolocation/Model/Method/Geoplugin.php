<?php

/**
 * Class Summa_Geolocation_Model_Method_Abstract
 *
 * @category Summa_Geolocation_Model_Method_Abstract
 * @package  Summa_Geolocation_Model_Method_Abstract
 * @author   Facundo Capua <fcapua@summasolutions.net>
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link     http://www.summasolutions.net/
 */
class Summa_Geolocation_Model_Method_Geoplugin
    extends Summa_Geolocation_Model_Method_Abstract
{
    public function getCountry()
    {
        $ip = $this->getUserIp();
        $geoPlugin = unserialize(file_get_contents('http://www.geoplugin.net/php.gp?ip='.$ip));

        return $geoPlugin["geoplugin_countryCode"];
    }
}