<?php

/**
 * Class Summa_Andreani_Model_Resource_Total_Insurance
 *
 * @category Summa
 * @package  Summa_Andreani
 * @author   Augusto Leao <aleao@summasolutions.net>
 * @license  http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @link     http://www.summasolutions.net/
 */

class Summa_Andreani_Model_Resource_Total_Insurance
    extends Mage_Core_Model_Mysql4_Abstract
{
    /**
     * Collection constructor
     */
    protected function _construct()
    {
        $this->_init('mage_sales/quote_address', 'summa_andreani_insurance');
    }

}