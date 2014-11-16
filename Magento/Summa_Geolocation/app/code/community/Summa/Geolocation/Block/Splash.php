<?php

/**
 * Class Summa_Geolocation_Block_Splash
 *
 * @category Summa
 * @package  Summa_Geolocation
 * @author   Facundo Capua <fcapua@summasolutions.net>
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link     http://www.summasolutions.net/
 */
class Summa_Geolocation_Block_Splash
    extends Mage_Core_Block_Template
{

    public function _prepareLayout()
    {
        $this->setTemplate('summa_geolocation/splash.phtml');

        return $this;
    }


    public function getStores()
    {
        $stores = Mage::getModel('core/store')->getCollection();
        $return = new Varien_Data_Collection();

        foreach ($stores as $store) {
            if (Mage::getStoreConfigFlag('general/country/include_splash', $store->getId())) {
                $object = new Varien_Object();
                $splashLabel = Mage::getStoreConfig('general/country/splash_label', $store);

                $params = array();
                if (!Mage::getStoreConfigFlag(Mage_Core_Model_Store::XML_PATH_STORE_IN_URL)) {
                    $params['_query']['___store'] = $store->getCode();
                }

                $data = array(
                    'object' => $store,
                    'url' => Mage::getUrl('/', $params),
                    'label' => $splashLabel ? $splashLabel : $store->getFrontendName()
                );
                $object->addData($data);
                $return->addItem($object);
            }
        }

        return $return;
    }
}