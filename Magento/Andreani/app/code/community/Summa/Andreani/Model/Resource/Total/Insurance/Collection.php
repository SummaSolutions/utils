<?php

/**
 * Class Summa_Andreani_Model_Resource_Total_Insurance_Collection
 *
 * @category Summa
 * @package  Summa_Andreani
 * @author   Augusto Leao <aleao@summasolutions.net>
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link     http://www.summasolutions.net/
 */

class Summa_Andreani_Model_Resource_Total_Insurance_Collection
    extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Collection constructor
     */
    protected function _construct()
    {
        $this->_init('sales/quote_address');
    }
}