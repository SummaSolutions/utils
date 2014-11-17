<?php

class Summa_AutoSuggest_Block_Results
    extends Mage_Core_Block_Template
{
    /**
     * @param $type
     * @return bool
     */
    public function isResultAllowed($type) {
        return (bool) $this->_getConfig($type."/show");
    }

    /**
     * @return mixed
     */
    public function getMinLength() {
        return $this->_getConfig("advanced/min_length");
    }

    /**
     * @return mixed
     */
    public function getDelay() {
        return $this->_getConfig("advanced/delay_ms");
    }

    /**
     * @return bool
     */
    public function isThumbnailAllowed() {
        return (bool) $this->_getConfig("products/display_thumbnail");
    }

    /**
     * @return mixed
     */
    public function getImageHeight() {
        return $this->_getConfig("products/thumbnail_height");
    }

    /**
     * @return mixed
     */
    public function getImageWidth() {
        return $this->_getConfig("products/thumbnail_width");
    }

    /**
     * @return bool
     */
    public function isDisplayCountActivated() {
        return (bool) $this->_getConfig("categories/display_count");
    }

    /**
     * @param $config
     * @return mixed
     */
    private function _getConfig($config) {
        return Mage::getStoreConfig("autosuggest/".$config);
    }
}