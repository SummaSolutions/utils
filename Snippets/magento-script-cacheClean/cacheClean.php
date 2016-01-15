<?php

require_once 'abstract.php';

class Mage_Shell_CacheClean extends Mage_Shell_Abstract
{
    /**
     * Run script
     *
     */
    public function run()
    {
        $app = Mage::app();
        if($app != null) {
            $cache = $app->getCache();
            if($cache != null) {
                $cache->clean();
                Mage::dispatchEvent('adminhtml_cache_flush_system');
            }
        }
    }
}

$shell = new Mage_Shell_CacheClean();
$shell->run();
