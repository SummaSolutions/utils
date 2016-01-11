<?php

class Summa_Badge_Helper_Data
    extends Mage_Core_Helper_Abstract
{
    /**
     * Retorna el numero de badges.
     * */
    public function getNumberBadges()
    {
        return Mage::getStoreConfig('summa_badge/number_badges');
    }

    /**
     * Retorna el html de las badges.
     * */
    public function getBadge($_product, $size)
    {
        $block = Mage::app()->getLayout()->createBlock('summa_badge/badge');
        $block->setProduct($_product);
        $block->setSize($size);

        return $block->toHtml();
    }
}