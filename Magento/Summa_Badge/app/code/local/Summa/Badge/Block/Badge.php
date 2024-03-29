<?php

class Summa_Badge_Block_Badge
    extends Mage_Core_Block_Template
{
    protected $_productId;

    protected function _construct()
    {
        parent::_construct();
        $this->setData('cache_lifetime', 86400);
        $this->setTemplate('summa_badge/badge.phtml');
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array(
            'BLOCK_BADGE',
            Mage::app()->getStore()->getCode(),
            $this->getTemplateFile(),
            'template' => $this->getTemplate(),
            'product_id' => $this->getProductId()
        );
    }

    public function getBadges($productId)
    {
        $_product = Mage::getModel('catalog/product')->load($productId);
        $badges = array();
        for ($i = 1; $i <= Mage::helper('summa_badge')->getNumberBadges(); $i++) {
            $attributeValue = $_product->getData('badge_' . $i);
            if ($attributeValue) {
                $badge = Mage::getModel('summa_badge/badge')->load($attributeValue);
                if ($badge->getId()) {
                    $badges[$i]['src'] = $badge->getUrl();
                    $badges[$i]['class'] = 'badge_' . $i;
                }
            }
        }

        return $badges;
    }

    public function setProductId($id)
    {
        $this->_productId = $id;
    }

    public function getProductId()
    {
        return $this->_productId;
    }

    public function getSrcBadgePdp()
    {
        $product = Mage::registry("product");
        $attributeValue = $product->getData('badge_pdp');
        if ($attributeValue) {
            $badge = Mage::getModel('summa_badge/badge')->load($attributeValue);
            if ($badge->getId()) {
                return $badge->getUrl();
            }
        }

        return;
    }
}