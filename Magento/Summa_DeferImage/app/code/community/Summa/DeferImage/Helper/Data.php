<?php

/**
 * Class Summa_DeferImage_Helper_Data
 *
 * @category Summa
 * @package  Summa_DeferImage
 * @author   Facundo Capua <fcapua@summasolutions.net>
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link     http://www.summasolutions.net/
 */
class Summa_DeferImage_Helper_Data
    extends Mage_Core_Helper_Abstract
{
    const XML_PATH_DEFER_IMAGES = 'catalog/defer_images/';

    public function isDeferImageEnable()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_DEFER_IMAGES . 'enable');
    }
}