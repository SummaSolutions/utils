<?php
/**
 * @author: Facundo Capua
 * Date: 5/3/13
 */

class Summa_AutoSuggest_Helper_Config extends Mage_Core_Helper_Abstract
{
    const CONFIG_PATH_PREFIX = 'autosuggest/';

    const CONFIG_PATH_PRODUCTS_LIMIT = 'products/limit';
    const CONFIG_PATH_PRODUCTS_DISPLAY_THUMBNAIL = 'products/display_thumbnail';
    const CONFIG_PATH_PRODUCTS_THUMBNAIL_WIDTH = 'products/thumbnail_width';
    const CONFIG_PATH_PRODUCTS_THUMBNAIL_HEIGHT = 'products/thumbnail_height';

    const CONFIG_PATH_CATEGORIES_LIMIT = 'categories/limit';
    const CONFIG_PATH_CATEGORIES_MIN_LEVEL = 'categories/min_level';
    const CONFIG_PATH_CATEGORIES_LINK_TYPE = 'categories/link_type';

    const CONFIG_PATH_PAGES_LIMIT = 'pages/limit';


    public function shouldDisplayProductsThumbnail()
    {
        $return = (bool) $this->getConfig(self::CONFIG_PATH_PRODUCTS_DISPLAY_THUMBNAIL);

        return $return;
    }

    public function getProductsCollectionLimit()
    {
        $return = (int) $this->getConfig(self::CONFIG_PATH_PRODUCTS_LIMIT);

        return $return;
    }

    public function getCategoriesCollectionLimit()
    {
        $return = (int) $this->getConfig(self::CONFIG_PATH_CATEGORIES_LIMIT);

        return $return;
    }

    public function getCmsPagesCollectionLimit()
    {
        $return = (int) $this->getConfig(self::CONFIG_PATH_PAGES_LIMIT);

        return $return;
    }


    public function getThumbnailDimension()
    {
        $width = $this->getConfig(self::CONFIG_PATH_PRODUCTS_THUMBNAIL_WIDTH);
        $height = $this->getConfig(self::CONFIG_PATH_PRODUCTS_THUMBNAIL_HEIGHT);

        return array($width, $height);
    }

    public function getMinimumCategoryLevel()
    {
        return $this->getConfig(self::CONFIG_PATH_CATEGORIES_MIN_LEVEL);
    }

    public function getCategoryLinkType()
    {
        return $this->getConfig(self::CONFIG_PATH_CATEGORIES_LINK_TYPE);
    }


    public function getConfig($config)
    {
        $path = self::CONFIG_PATH_PREFIX.$config;

        return Mage::getStoreConfig($path);
    }
}