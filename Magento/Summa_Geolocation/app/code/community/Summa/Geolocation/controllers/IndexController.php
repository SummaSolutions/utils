<?php

/**
 * Class Summa_Geolocation_IndexController
 *
 * @category Summa
 * @package  Summa_Geolocation
 * @author   Facundo Capua <fcapua@summasolutions.net>
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link     http://www.summasolutions.net/
 */
class Summa_Geolocation_IndexController
    extends Mage_Core_Controller_Front_Action
{
    public function indexAction()
    {

        $helper = Mage::helper('summa_geolocation');

        if (!$helper->isGeolocationEnable()) {
            $this->_redirectUrl('/');

            return $this;
        }

        $userCountryCode = $helper->getUserCountryCode();
        $store = $helper->getStoreByCountry($userCountryCode);
        if ($store) {
            $params = array();
            if (!Mage::getStoreConfigFlag(Mage_Core_Model_Store::XML_PATH_STORE_IN_URL)) {
                $params['_query']['___store'] = $store->getCode();
            }
            $this->_redirectUrl(Mage::getUrl('/', $params));

            return $this;
        }

        if ($helper->isSplashEnable()) {
            $this->_redirectUrl($helper->getSplashUrl());
        }

        return $this;
    }
}