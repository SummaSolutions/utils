<?php

class Summa_Badge_Block_Adminhtml_Catalog_Product_Tab
    extends Mage_Adminhtml_Block_Widget
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{

    protected $_fieldSuffix = 'product';

    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('summa_badge/catalog/product/tab.phtml');
        $this->_setTabPosition();
    }

    public function getFileBrowserUrl()
    {
        return Mage::getSingleton('adminhtml/url')->getUrl('/cms_wysiwyg_images/index');
    }


    /**
     * Position badges after tab "Images"
     */
    protected function _setTabPosition()
    {
        $attributeSetId = $this->getProduct()->getAttributeSetId();
        if ($attributeSetId) {
            $group = Mage::getModel('eav/entity_attribute_group')->getCollection();
            $group->addFieldToSelect('attribute_group_id')
                ->addFieldToFilter('attribute_set_id', array('eq' => $attributeSetId))
                ->addFieldToFilter('attribute_group_name', array('eq' => 'Images'));
            $group = $group->getFirstItem();

            if ($group->getId()) {
                $this->setAfter('group_' . $group->getId());
            }
        }
    }

    public function getTabLabel()
    {
        return $this->__('Badges');
    }

    public function getTabTitle()
    {
        return $this->__('Badges');
    }

    /**
     * Determines whether to display the tab
     *
     * @return bool
     */
    public function canShowTab()
    {
        if (!($setId = $this->getProduct()->getAttributeSetId())) {
            $setId = $this->getRequest()->getParam('set', null);
        }

        if ($setId) {
            return Mage::getSingleton('admin/session')->isAllowed('catalog/products/summa_badge');
        } else {
            $product = Mage::app()->getRequest()->getParam('product');
            if (!empty($product)) {
                return Mage::getSingleton('admin/session')->isAllowed('catalog/products/summa_badge');
            }
        }

        return false;
    }

    /**
     * Stops the tab being hidden
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    public function setFieldSuffix($suffix)
    {
        $this->_fieldSuffix = $suffix;
    }

    public function getFieldSuffix()
    {
        return $this->_fieldSuffix;
    }

    public function getBadgeOption($number)
    {
        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'badge_' . $number);

        return $attribute->getSource()->getAllOptions();
    }

    public function getBadgePdpOption()
    {
        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'badge_pdp');

        return $attribute->getSource()->getAllOptions();
    }

    /**
     * return the label configurated in attribute
     *
     * @param $number
     *
     * @return string
     */
    public function getBadgeLabel($number)
    {
        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'badge_' . $number);

        return $attribute->getStoreLabel();
    }

    public function getBadgePdpLabel()
    {
        $attribute = Mage::getSingleton('eav/config')->getAttribute('catalog_product', 'badge_pdp');

        return $attribute->getStoreLabel();
    }

    public function getProduct()
    {
        $product = Mage::registry('product');
        if (!$product) {
            $product = Mage::getModel('catalog/product');
        }

        return $product;
    }

    public function getFieldValue($field)
    {
        return $this->getProduct()->getData($field);
    }
}