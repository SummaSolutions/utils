<?php
/**
 *
 * @author: mhidalgo@summasolutions.net
 * Date:    29/01/16
 * Time:    09:59
 * @copyright   Copyright (c) 2016 Summa Solutions (http://www.summasolutions.net)
 */
class Summa_ConfigurableSwatches_Block_Catalog_Layer_State_Swatch
    extends Mage_ConfigurableSwatches_Block_Catalog_Layer_State_Swatch
{
    /**
     * Set one-time data on the renderer
     *
     * @param Mage_Catalog_Model_Layer_Filter_Item $filter
     */
    protected function _init($filter)
    {
        parent::_init($filter);
        $this->_initDone = false;
    }
}