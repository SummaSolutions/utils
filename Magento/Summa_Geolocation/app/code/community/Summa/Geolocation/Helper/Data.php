<?php

/**
 * Class Summa_Geolocation_Helper_Data
 *
 * @category Summa
 * @package  Summa_Geolocation
 * @author   Facundo Capua <fcapua@summasolutions.net>
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link     http://www.summasolutions.net/
 */
class Summa_Geolocation_Helper_Data
    extends Mage_Core_Helper_Abstract
{
    const METHOD_PHP = 'php';
    const METHOD_GEOPLUGIN = 'geoplugin';

    public function getMethodsAvailable()
    {
        return array(
            self::METHOD_PHP       => $this->__('PHP Library - Faster (recommended)'),
            self::METHOD_GEOPLUGIN => $this->__('Geoplugin Web Service - No extra library needed')
        );
    }

    public function isGeolocationEnable()
    {
        return Mage::getStoreConfigFlag('general/country/use_geolocation');
    }

    public function isSplashEnable()
    {
        return Mage::getStoreConfigFlag('general/country/use_splash');
    }

    public function getSplashStore()
    {
        $storeId = Mage::getStoreConfig('general/country/splash_store');
        $store = Mage::app()->getStore();
        if(!empty($storeId)){
            $store = Mage::getModel('core/store')->load($storeId);
        }

        return $store;
    }

    public function getSplashUrl()
    {
        $url = Mage::getStoreConfig('general/country/splash_url');
        $store = $this->getSplashStore();
        if(empty($url)){
            $url = '/';
        }

        $params = array();
        if (!Mage::getStoreConfigFlag(Mage_Core_Model_Store::XML_PATH_STORE_IN_URL)) {
            $params['_query']['___store'] = $store->getCode();
        }

        return Mage::getUrl($url, $params);
    }

    /**
     * @return Summa_Geolocation_Model_Method_Abstract
     */
    public function getMethodInstance()
    {
        $method = Mage::getStoreConfig('general/country/method');
        $instance = Mage::getModel('summa_geolocation/method_' . $method);

        return $instance;
    }

    public function getUserCountryCode()
    {
        $countryCode = Mage::getSingleton('core/cookie')->get('country_code');
        if (empty($countryCode)) {
            $methodInstance = $this->getMethodInstance();
            $countryCode = $methodInstance->getCountry();
            Mage::getSingleton('core/cookie')->set('country_code', $countryCode);
        }

        return $countryCode;
    }

    public function getStoreByCountry($countryCode)
    {
        $stores = Mage::getModel('core/store')->getCollection();

        foreach ($stores as $store) {
            $useGeolocation = Mage::getStoreConfigFlag('general/country/include_store', $store->getId());
            if ($useGeolocation && Mage::getStoreConfig('general/country/default', $store->getId()) ==  $countryCode) {
                return $store;
            }
        }

        return null;
    }
}