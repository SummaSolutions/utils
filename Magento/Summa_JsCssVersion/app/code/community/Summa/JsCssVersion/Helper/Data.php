<?php

/**
 * Summa_JsCssVersion_Helper_Data
 *
 * @category Summa
 * @package  Summa_JsCssVersion
 * @author   Facundo Capua <fcapua@summasolutions.net>
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link     http://www.summasolutions.net/
 */
class Summa_JsCssVersion_Helper_Data
    extends Mage_Core_Helper_Abstract
{
    const CONFIG_PATH_PREFIX = 'dev/summa_jscss_version/';

    const VERSION_QUERYSTRING_PARAM = 'v';

    public function isEnabled()
    {
        return Mage::getStoreConfigFlag(self::CONFIG_PATH_PREFIX.'enable');
    }

    public function getVersionString()
    {
        $version = '';
        if($this->isEnabled()){
            $version = '?';
            $version .= self::VERSION_QUERYSTRING_PARAM.'=';
            $version .= md5(Mage::getStoreConfig(self::CONFIG_PATH_PREFIX.'version'));
        }

        return $version;
    }
}